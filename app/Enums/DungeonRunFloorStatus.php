<?php

declare(strict_types=1);

namespace App\Enums;

enum DungeonRunFloorStatus: string
{
    case ACTIVE    = 'active';
    case COMPLETED = 'completed';
    case FAILED    = 'failed';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE    => 'Активный',
            self::COMPLETED => 'Пройден',
            self::FAILED    => 'Провалено',
        };
    }
}