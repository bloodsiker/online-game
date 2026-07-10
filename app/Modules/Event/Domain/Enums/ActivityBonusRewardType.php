<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Enums;

enum ActivityBonusRewardType: string
{
    case MONEY = 'money';
    case DIAMOND = 'diamond';
    case ITEM = 'item';

    public function label(): string
    {
        return match ($this) {
            self::MONEY => 'Монеты',
            self::DIAMOND => 'Бриллианты',
            self::ITEM => 'Предмет',
        };
    }
}
