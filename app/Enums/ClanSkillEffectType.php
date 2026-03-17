<?php

namespace App\Enums;

enum ClanSkillEffectType: string
{
    case STR     = 'str';
    case INT     = 'int';
    case AGI     = 'agil';
    case HP_MAX  = 'hp_max';
    case MP_MAX  = 'mp_max';
    case ATTACK = 'attack';
    case DEFENSE = 'defense';

    public function label(): string
    {
        return match($this) {
            self::STR     => 'Сила',
            self::INT     => 'Интеллект',
            self::AGI     => 'Ловкость',
            self::HP_MAX  => 'Макс. HP',
            self::MP_MAX  => 'Макс. MP',
            self::ATTACK  => 'Атака',
            self::DEFENSE => 'Защита',
        };
    }
}
