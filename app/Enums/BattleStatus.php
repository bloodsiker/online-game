<?php

namespace App\Enums;

enum BattleStatus: int
{
    case FINISH = 0;
    case ACTIVE = 1;

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isFinish(): bool
    {
        return $this === self::FINISH;
    }
}
