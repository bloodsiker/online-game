<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\Mappers;

use App\Modules\Event\Application\DTOs\ActivityCardDTO;
use App\Modules\Event\Domain\Enums\ActivityBonusRewardType;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivity;

class ActivityCardViewMapper
{
    public function map(EventActivity $activity, int $progress): ActivityCardDTO
    {
        return new ActivityCardDTO(
            title: $activity->title,
            rewardItemId: $activity->reward_share_item_id,
            icon: ltrim($activity->rewardItem->image ?? '', '/'),
            iconCount: $activity->reward_item_amount,
            progressCurrent: min($progress, $activity->required_count),
            progressTotal: $activity->required_count,
            descriptionHtml: $activity->description,
            bonusHtml: $this->bonusHtml($activity),
        );
    }

    private function bonusHtml(EventActivity $activity): ?string
    {
        if ($activity->bonus_reward_type === null || $activity->bonus_reward_amount === null) {
            return null;
        }

        return match ($activity->bonus_reward_type) {
            ActivityBonusRewardType::MONEY => $this->currencyHtml('Монеты', 'm_game.gif', $activity->bonus_reward_amount),
            ActivityBonusRewardType::DIAMOND => $this->currencyHtml('Диамант', 'm_dmd.gif', $activity->bonus_reward_amount),
            ActivityBonusRewardType::ITEM => $this->bonusItemHtml($activity),
        };
    }

    private function currencyHtml(string $title, string $icon, int $amount): string
    {
        return '<span title="'.e($title).'"><img src="'.asset('img/icon/'.$icon).'" width="11" height="11" align="absmiddle"></span>&nbsp;'.$amount;
    }

    private function bonusItemHtml(EventActivity $activity): ?string
    {
        $item = $activity->bonusRewardItem;

        if ($item === null) {
            return null;
        }

        $infoUrl = route('items.info.share', ['id' => $item->id]);
        $color = e($item->rarity?->color() ?? '#3300ff');
        $name = e($item->name).($activity->bonus_reward_amount > 1 ? ' ×'.$activity->bonus_reward_amount : '');

        return '<b><a href="#" style="color:'.$color.';"'
            .' onclick="window.open(\''.$infoUrl.'\', \'\', \'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;">'
            .$name.'</a></b>';
    }
}
