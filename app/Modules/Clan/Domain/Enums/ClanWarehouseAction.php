<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Enums;

enum ClanWarehouseAction: string
{
    case PUT = 'put';
    case TAKE = 'take';

    public function isPut(): bool
    {
        return $this === self::PUT;
    }

    public function isTake(): bool
    {
        return $this === self::TAKE;
    }

    public function label(): string
    {
        return match ($this) {
            self::PUT => 'Положил',
            self::TAKE => 'Забрал',
        };
    }
}
