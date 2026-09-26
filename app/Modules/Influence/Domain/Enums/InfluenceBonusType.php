<?php

declare(strict_types=1);

namespace App\Modules\Influence\Domain\Enums;

enum InfluenceBonusType: string
{
    case GATHERING_SPEED_PERCENT = 'gathering_speed_percent';
    case BONUS_RESOURCE_CHANCE_PERCENT = 'bonus_resource_chance_percent';
    case MONSTER_MONEY_PERCENT = 'monster_money_percent';

    public function label(): string
    {
        return match ($this) {
            self::GATHERING_SPEED_PERCENT => 'Скорость добычи, %',
            self::BONUS_RESOURCE_CHANCE_PERCENT => 'Шанс дополнительного ресурса, %',
            self::MONSTER_MONEY_PERCENT => 'Монеты с местных монстров, %',
        };
    }
}
