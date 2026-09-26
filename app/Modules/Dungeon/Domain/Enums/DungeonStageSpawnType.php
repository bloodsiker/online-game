<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Enums;

enum DungeonStageSpawnType: string
{
    case DISTRIBUTED = 'distributed';
    case FIXED = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::DISTRIBUTED => 'Распределить по локациям',
            self::FIXED => 'Фиксированные локации',
        };
    }
}
