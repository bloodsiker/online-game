<?php

declare(strict_types=1);

namespace App\Modules\Quest\Presentation\Services;

use App\Modules\Quest\Domain\Enums\QuestRewardType;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;

final class QuestRewardPresenter
{
    public function format(QuestReward $reward): string
    {
        return match ($reward->type) {
            QuestRewardType::EXP => '+'.$reward->amount.' '.__('опыта'),
            QuestRewardType::MONEY => '+'.$reward->amount.' '.__('монет'),
            QuestRewardType::ITEM => ($reward->amount > 1 ? $reward->amount.'x ' : '')
                .($reward->itemInfo?->name ?? __('предмет')),
            QuestRewardType::LOCATION_ACCESS => __('Открыт доступ:').' '.($reward->location?->name ?? __('локация')),
            QuestRewardType::CLAN_POINTS => '+'.$reward->amount.' '.__('клановых очков'),
            QuestRewardType::REPUTATION_POINTS => '+'.$reward->amount.' '.__('очков репутации'),
        };
    }
}
