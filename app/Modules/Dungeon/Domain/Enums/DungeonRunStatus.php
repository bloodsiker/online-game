<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Enums;

enum DungeonRunStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case ABANDONED = 'abandoned';
}
