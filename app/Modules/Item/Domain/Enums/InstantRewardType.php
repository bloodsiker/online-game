<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Enums;

enum InstantRewardType: string
{
    case MONEY = 'money';

    public function label(): string
    {
        return match ($this) {
            self::MONEY => 'Монеты',
        };
    }
}
