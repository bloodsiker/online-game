<?php

declare(strict_types=1);

namespace App\Enums;

enum DungeonRunStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case ABANDONED = 'abandoned';

    public function isTerminal(): bool
    {
        return match ($this) {
            self::COMPLETED, self::FAILED, self::ABANDONED => true,
            self::ACTIVE => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Активный',
            self::COMPLETED => 'Пройден',
            self::FAILED => 'Провалено',
            self::ABANDONED => 'Заброшен',
        };
    }
}
