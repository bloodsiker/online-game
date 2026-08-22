<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Enums;

enum ItemActionType: string
{
    case DROP = 'drop';
    case SELL = 'sell';
    case GIVE = 'give';

    public function label(): string
    {
        return match ($this) {
            self::DROP => 'Выброшен',
            self::SELL => 'Продан',
            self::GIVE => 'Передан',
        };
    }
}
