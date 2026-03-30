<?php

declare(strict_types=1);

namespace App\Enums;

enum ShareItemRequirementType: string
{
    case LEVEL = 'level';
    case STAT = 'stat';
    case SKILL = 'skill';

    public function label(): string
    {
        return match ($this) {
            self::LEVEL => 'Уровень персонажа',
            self::STAT => 'Характеристика',
            self::SKILL => 'Навык',
        };
    }
}
