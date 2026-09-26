<?php

declare(strict_types=1);

namespace App\Modules\Influence\Domain\Enums;

enum InfluenceRewardType: string
{
    case MONEY = 'money';
    case DIAMOND = 'diamond';
    case ITEM = 'item';
    case EXPERIENCE = 'experience';

    public function label(): string
    {
        return match ($this) {
            self::MONEY => 'Монеты',
            self::DIAMOND => 'Алмазы',
            self::ITEM => 'Предмет',
            self::EXPERIENCE => 'Опыт',
        };
    }
}
