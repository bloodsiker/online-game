<?php

namespace App\Enums;

enum QuestPlayerStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::IN_PROGRESS => 'В процессе',
            self::COMPLETED => 'Завершен',
            self::FAILED => 'Провален',
        };
    }

    public function isProgress(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }
}
