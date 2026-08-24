<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Domain\Enums;

enum MagicSkillRequirementType: string
{
    /** Старый тип, оставлен только чтобы записи до миграции не вызывали 500. */
    case LEVEL = 'level';
    case STAT = 'stat';
    case SKILL = 'skill';

    public function label(): string
    {
        return match ($this) {
            self::LEVEL => 'Устаревшее требование уровня',
            self::STAT => 'Характеристика',
            self::SKILL => 'Навык',
        };
    }
}
