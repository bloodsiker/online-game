<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Enums;

enum DungeonCooldownType: string
{
    case PERSONAL = 'personal';
    case GLOBAL = 'global';

    public function label(): string
    {
        return match ($this) {
            self::PERSONAL => 'Личный',
            self::GLOBAL => 'Общий',
        };
    }
}
