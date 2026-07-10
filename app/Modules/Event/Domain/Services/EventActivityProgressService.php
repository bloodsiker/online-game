<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Event\Domain\Enums\ActivityBonusRewardType;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivity;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivityProgress;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\DB;

class EventActivityProgressService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ChatService $chatService,
    ) {}

    /**
     * Продвигает прогресс всех активных активностей игрока, привязанных к убитому монстру,
     * и выдаёт награду автоматически, как только требуемое количество достигнуто.
     */
    public function progressKill(Player $player, MonsterOnLocation $locationMonster): void
    {
        $activities = EventActivity::query()
            ->where('is_active', true)
            ->where('monster_id', $locationMonster->monster_id)
            ->get();

        foreach ($activities as $activity) {
            $this->progressActivity($player, $activity);
        }
    }

    private function progressActivity(Player $player, EventActivity $activity): void
    {
        DB::transaction(function () use ($player, $activity): void {
            $progress = EventActivityProgress::query()
                ->lockForUpdate()
                ->firstOrCreate([
                    'user_id' => $player->user_id,
                    'event_activity_id' => $activity->id,
                    'period_key' => $activity->period->currentKey(),
                ]);

            if ($progress->isCompleted() || $progress->progress >= $activity->required_count) {
                return;
            }

            $progress->increment('progress');

            if ($progress->progress < $activity->required_count) {
                return;
            }

            $progress->completed_at = now();
            $progress->save();

            $rewardParts = $this->grantReward($player, $activity);

            $progress->reward_claimed_at = now();
            $progress->save();

            $this->chatService->sendSystemToUser(
                $player->user,
                "Активность «{$activity->title}» выполнена! Награда: ".implode(' | ', $rewardParts),
            );
        });
    }

    /**
     * Выдаёт награды и возвращает их шорткоды для сообщения в чат
     * ([[item_N]], [[money_N]], [[diamond_N]] — рендерятся MessageRenderer'ом).
     *
     * @return list<string>
     */
    private function grantReward(Player $player, EventActivity $activity): array
    {
        $backpack = $this->backpackService->addItemByShareItem($player->user, $activity->rewardItem, $activity->reward_item_amount);

        $parts = [
            "[[item_{$backpack->item_id}]]".($activity->reward_item_amount > 1 ? " {$activity->reward_item_amount}шт" : ''),
        ];

        if ($activity->bonus_reward_type === null || $activity->bonus_reward_amount === null) {
            return $parts;
        }

        $bonusPart = match ($activity->bonus_reward_type) {
            ActivityBonusRewardType::MONEY => $this->grantCurrency($player, 'money', $activity->bonus_reward_amount),
            ActivityBonusRewardType::DIAMOND => $this->grantCurrency($player, 'diamond', $activity->bonus_reward_amount),
            ActivityBonusRewardType::ITEM => $this->grantBonusItem($player, $activity),
        };

        if ($bonusPart !== null) {
            $parts[] = $bonusPart;
        }

        return $parts;
    }

    private function grantCurrency(Player $player, string $currency, int $amount): string
    {
        $player->user->increment($currency, $amount);

        return "[[{$currency}_{$amount}]]";
    }

    private function grantBonusItem(Player $player, EventActivity $activity): ?string
    {
        if ($activity->bonusRewardItem === null) {
            return null;
        }

        $backpack = $this->backpackService->addItemByShareItem($player->user, $activity->bonusRewardItem, $activity->bonus_reward_amount);

        return "[[item_{$backpack->item_id}]]".($activity->bonus_reward_amount > 1 ? " {$activity->bonus_reward_amount}шт" : '');
    }
}
