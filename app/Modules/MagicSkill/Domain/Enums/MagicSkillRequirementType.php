<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Domain\Enums;

enum MagicSkillRequirementType: string
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
