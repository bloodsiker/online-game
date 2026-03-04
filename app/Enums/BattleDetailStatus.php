<?php

namespace App\Enums;

enum BattleDetailStatus: int
{
    case DEATH = 0;
    case LIFE = 1;

    public function isLife(): bool
    {
        return $this === self::LIFE;
    }

    public function isDeath(): bool
    {
        return $this === self::DEATH;
    }
}
