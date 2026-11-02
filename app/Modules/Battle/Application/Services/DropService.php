<?php

namespace App\Modules\Battle\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
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
    ) {}

    public function dropMoney(User $user, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        if ($locationMonster->is_drop_money) {
            return;
        }

        $monster = $locationMonster->monster;
        if ($monster->min_money > 0 && $monster->max_money > 0) {
            $money = mt_rand($monster->min_money, $monster->max_money);
            $bonusPercent = $this->influenceBonusService
                ->for($user->id, (int) $locationMonster->location->map_id)
                ->monsterMoneyPercent;
            $bonusPercent += $this->premiumBonusService
                ->value($user->id, PremiumBonus::MONSTER_MONEY);
            $money = max(1, (int) round($money * (1 + $bonusPercent / 100)));
            $user->increment('money', $money);
            $result->log(sprintf("<p style='margin:2px 0;'><span style='background:#fde8e8; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>⚔️ <b style='color:#7b1a1a;'>%s</b> уничтожен. Найдено <b>%s</b> монет</span></p>", $locationMonster->monster->name, $money));
        } else {
            $result->log(sprintf("<p style='margin:2px 0;'><span style='background:#fde8e8; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>⚔️ <b style='color:#7b1a1a;'>%s</b> уничтожен</span></p>", $locationMonster->monster->name));
        }

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

                $recipient = $this->monsterLootService->recipient($battle->id, $monster->location_monster_id, $dropRecipient);
                $recipients[$recipient->id]['user'] ??= $recipient;
                $recipients[$recipient->id]['monsters'][] = $monster->locationMonster;
            }
            foreach ($recipients as $group) {
                $this->dropMonsterItems($group['monsters'], $location, $group['user'], $dungeonSessionId);
            }
        }
    }

    private function dropMonsterItems(array $monsters, Location $location, User $dropRecipient, ?int $dungeonSessionId): void
    {
        $droppedItems = [];
        foreach ($monsters as $monster) {
            foreach ($monster->monster->items as $item) {
                if ($item->max_drop_level_difference !== null && $dropRecipient->player->lvl - $monster->monster->lvl > $item->max_drop_level_difference) {
                    continue;
                }
                if ($item->required_active_effect_id !== null && ! $this->hasActiveEffect($dropRecipient, $item->required_active_effect_id)) {
                    continue;
                }
                if (random_int(1, 100000) / 1000 <= $item->pivot->drop_chance) {
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
}
