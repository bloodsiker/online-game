<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\Enums;

enum QuestRewardType: string
{
    case EXP = 'exp';
    case MONEY = 'money';
    case ITEM = 'item';
    case LOCATION_ACCESS = 'location_access';
    case CLAN_POINTS = 'clan_points';
    case REPUTATION_POINTS = 'reputation_points';

    public function label(): string
    {
        return match ($this) {
            self::EXP => 'Опыт',
            self::MONEY => 'Монеты',
            self::ITEM => 'Предмет',
            self::LOCATION_ACCESS => 'Доступ к локации',
            self::CLAN_POINTS => 'Очки клана',
            self::REPUTATION_POINTS => 'Очки репутации',
        };
    }
}
