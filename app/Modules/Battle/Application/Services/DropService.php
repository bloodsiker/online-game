<?php

namespace App\Modules\Battle\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Effect\Domain\Services\EffectBonusService;
use App\Modules\Influence\Domain\Services\MapInfluenceBonusService;
use App\Modules\Item\Domain\Enums\LocationItemInteractionType;
use App\Modules\Item\Domain\Services\InstantItemRewardService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Premium\Application\Services\PremiumBonusService;
use App\Modules\Premium\Domain\Enums\PremiumBonus;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Carbon;

class DropService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly MapInfluenceBonusService $influenceBonusService,
        private readonly PremiumBonusService $premiumBonusService,
        private readonly ChatService $chatService,
        private readonly InstantItemRewardService $instantRewardService,
        private readonly GameSettingsService $gameSettingsService,
        private readonly MonsterLootService $monsterLootService = new MonsterLootService,
        private readonly ?BattlePhraseBook $phraseBook = null,
    ) {}

    /**
     * Монеты с монстра делятся между всеми, кто его бил, пропорционально урону (см. MonsterLootService::moneyShares).
     * Личное сообщение в чат получают только за боссов (за обычных мобов не пишем, чтобы не раздувать таблицу чата);
     * убийца видит свою долю в строке гибели в логе боя.
     */
    public function dropMoney(User $killer, MonsterOnLocation $locationMonster, AttackResultDTO $result, ?int $battleId = null): void
    {
        if ($locationMonster->is_drop_money) {
            return;
        }

        $monster = $locationMonster->monster;
        $killerMoney = null;

        if ($monster->min_money > 0 && $monster->max_money > 0) {
            $total = mt_rand($monster->min_money, $monster->max_money);
            $battleId ??= (int) BattleDetail::query()->where('location_monster_id', $locationMonster->id)->latest('id')->value('battle_id');
            $mapId = (int) $locationMonster->location->map_id;

            foreach ($this->monsterLootService->moneyShares($battleId, (int) $locationMonster->id, $total, (int) $killer->id) as $share) {
                $user = $share['user_id'] === (int) $killer->id ? $killer : User::without('player')->find($share['user_id']);
                if ($user === null) {
                    continue;
                }
                $bonusPercent = $this->influenceBonusService->for($user->id, $mapId)->monsterMoneyPercent
                    + $this->premiumBonusService->value($user->id, PremiumBonus::MONSTER_MONEY);
                $money = max(1, (int) round($share['amount'] * (1 + $bonusPercent / 100)));
                $user->increment('money', $money);

                if ($user->id === $killer->id) {
                    $killerMoney = $money;
                }
                if ($monster->isBoss()) {
                    $this->chatService->sendLootToUser($user, new LocalizedChatMessage('chat.monster_money_share', [
                        'amount' => format_money($money),
                        'percent' => $share['percent'],
                        'monster' => LocalizedChatMessage::contentName($monster),
                    ]));
                }
            }
        }

        $result->log($this->deathLog($monster, $killerMoney));

        $locationMonster->is_drop_money = 1;
        $locationMonster->save();
    }

    public function dropItemsFromMonsters(Battle $battle, Location $location, User $dropRecipient, ?int $dungeonSessionId = null): void
    {
        if ($battle->status->isFinish()) {
            $detailsWithMonsters = BattleDetail::with('locationMonster.monster.items.instantReward')
                ->whereNotNull('location_monster_id')
                ->where('battle_id', $battle->id)->get();

            $recipients = [];
            foreach ($detailsWithMonsters as $monster) {
                if (! $monster->locationMonster?->monster instanceof Monster) {
                    continue;
                }

                $owner = $this->monsterLootService->lootOwner($battle->id, $monster->location_monster_id, $dropRecipient);
                $recipient = $owner['user'];
                $recipients[$recipient->id]['user'] ??= $recipient;
                $recipients[$recipient->id]['party_id'] ??= $owner['party_id'];
                $recipients[$recipient->id]['monsters'][] = $monster->locationMonster;
            }
            foreach ($recipients as $group) {
                $this->dropMonsterItems($group['monsters'], $location, $group['user'], $dungeonSessionId, $group['party_id'] ?? null);
            }
        }
    }

    private function dropMonsterItems(array $monsters, Location $location, User $dropRecipient, ?int $dungeonSessionId, ?int $lootPartyId = null): void
    {
        $droppedItems = [];
        // «Эликсир удачи охотника»: +% к шансу дропа (кроме сундуков и предметов под эффект).
        $recipientPlayerId = $dropRecipient->player?->id;
        $luckPercent = $recipientPlayerId !== null
            ? app(EffectBonusService::class)->percent((int) $recipientPlayerId, EffectBonusService::DROP_CHANCE)
            : 0.0;
        foreach ($monsters as $monster) {
            foreach ($monster->monster->items as $item) {
                if ($item->max_drop_level_difference !== null && $dropRecipient->player->lvl - $monster->monster->lvl > $item->max_drop_level_difference) {
                    continue;
                }
                if ($item->required_active_effect_id !== null && ! $this->hasActiveEffect($dropRecipient, $item->required_active_effect_id)) {
                    continue;
                }
                $chance = (float) $item->pivot->drop_chance;
                if ($luckPercent > 0 && $item->type !== ShareItemType::CHEST && $item->required_active_effect_id === null) {
                    $chance *= 1 + $luckPercent / 100;
                }
                if (random_int(1, 100000) / 1000 <= $chance) {
                    $droppedItems[] = ['item' => $item, 'count' => mt_rand($item->pivot->min_count, $item->pivot->max_count)];
                }
            }
        }

        $autoLoot = $this->premiumBonusService->canAutoLoot($dropRecipient->id)
            && $this->gameSettingsService->get($dropRecipient->id)['auto_loot_enabled'];
        $autoLooted = [];
        $autoLootedMoney = 0;
        foreach ($droppedItems as $dropItem) {
            $isChest = $dropItem['item']->type === ShareItemType::CHEST;

            if ($autoLoot) {
                $reward = $this->instantRewardService->grant($dropRecipient, $dropItem['item'], $dropItem['count']);
                if ($reward !== null) {
                    $autoLootedMoney += $reward->amount;
                } else {
                    $this->backpackService->addItemByShareItem($dropRecipient, $dropItem['item'], $dropItem['count']);
                    $autoLooted[$dropItem['item']->id] = ($autoLooted[$dropItem['item']->id] ?? 0) + $dropItem['count'];
                }

                continue;
            }

            if ($dropItem['item']->drop_direct_to_backpack && ! $isChest) {
                $this->backpackService->addItemByShareItem($dropRecipient, $dropItem['item'], $dropItem['count']);

                continue;
            }

            $item = new Item;
            $item->share_item_id = $dropItem['item']->id;
            $item->count_use = $dropItem['item']->count_use;
            $item->save();

            $pivotData = [
                'count' => $dropItem['count'],
                'loot_owner_id' => $dropRecipient->id,
                'loot_party_id' => $lootPartyId,
                'loot_reserved_until' => now()->addMinute(),
                'interaction_type' => LocationItemInteractionType::PICKUP->value,
                'expires_at' => $dropItem['item']->groundExpiresAt(),
            ];
            if ($dungeonSessionId !== null) {
                $pivotData['dungeon_session_id'] = $dungeonSessionId;
            }

            $location->itemsOnLocation()->attach($item->id, $pivotData);
        }

        foreach ($autoLooted as $shareItemId => $count) {
            $this->chatService->sendLootToUser($dropRecipient, new LocalizedChatMessage('chat.monster_auto_loot', [
                'item_id' => $shareItemId,
                'count' => $count,
            ]));
        }
        if ($autoLootedMoney > 0) {
            $this->chatService->sendLootToUser($dropRecipient, new LocalizedChatMessage('chat.monster_auto_loot_money', [
                'amount' => format_money($autoLootedMoney),
            ]));
        }
    }

    private function hasActiveEffect(User $user, int $effectId): bool
    {
        return PlayerActiveEffect::query()
            ->where('player_id', $user->player->id)
            ->where('effect_id', $effectId)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now()))
            ->exists();
    }

    /** «⚔️ Кабан падает замертво. Найдено 83 монет» — фраза гибели по стилю атаки монстра. */
    private function deathLog(Monster $monster, ?int $money): string
    {
        $phrases = $this->phraseBook ?? app(BattlePhraseBook::class);

        return BattleLogTranslator::format(
            $phrases->monsterDeath($monster->attack_style, $money !== null),
            BattleLogTranslator::contentName($monster),
            $money ?? 0,
        );
    }
}
