<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Enums;

enum ClanSkillEffectType: string
{
    case STRENGTH = 'strength';
    case INTUITION = 'intuition';
    case AGILITY = 'agility';
    case INTELLIGENCE = 'intelligence';
    case WISDOM = 'wisdom';
    case HP_MAX = 'hp_max';
    case MP_MAX = 'mp_max';
    case ATTACK = 'attack';
    case DEFENSE = 'defense';

    public function label(): string
    {
        return match ($this) {
            self::STRENGTH => 'Сила',
            self::INTUITION => 'Интуиция',
            self::AGILITY => 'Ловкость',
            self::INTELLIGENCE => 'Интеллект',
            self::WISDOM => 'Мудрость',
            self::HP_MAX => 'Макс. HP',
            self::MP_MAX => 'Макс. MP',
            self::ATTACK => 'Атака',
            self::DEFENSE => 'Защита',
        };
    }
}
