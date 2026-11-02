<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Domain\Enums;

enum MagicSkillRequirementType: string
{
    /** Гейт по уровню персонажа — для классонейтральных скиллов (пассивки), не завязанных на конкретный навык вроде «Колдовства». */
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
