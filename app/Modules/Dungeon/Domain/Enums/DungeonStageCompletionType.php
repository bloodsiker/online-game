<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Enums;

enum DungeonStageCompletionType: string
{
    case KILL_ALL = 'kill_all';

    public function label(): string
    {
        return match ($this) {
            self::KILL_ALL => 'Уничтожить всех монстров',
        };
    }
}
