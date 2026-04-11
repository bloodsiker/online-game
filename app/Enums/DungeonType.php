<?php

declare(strict_types=1);

namespace App\Enums;

enum DungeonType: string
{
    case LINEAR    = 'linear';
    case SURVIVAL  = 'survival';
    case BOSS_RUSH = 'boss_rush';

    public function label(): string
    {
        return match($this) {
            self::LINEAR    => 'Линейное',
            self::SURVIVAL  => 'Выживание',
            self::BOSS_RUSH => 'Натиск боссов',
        };
    }
}