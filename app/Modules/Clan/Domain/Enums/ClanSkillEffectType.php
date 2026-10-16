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
    case MAGIC_RESISTANCE = 'magic_resistance';

    public function label(): string
    {
        return match ($this) {
            self::STRENGTH => __('Сила'),
            self::INTUITION => __('Интуиция'),
            self::AGILITY => __('Ловкость'),
            self::INTELLIGENCE => __('Интеллект'),
            self::WISDOM => __('Мудрость'),
            self::HP_MAX => __('Макс. HP'),
            self::MP_MAX => __('Макс. MP'),
            self::ATTACK => __('Атака'),
            self::DEFENSE => __('Защита'),
            self::MAGIC_RESISTANCE => __('Магическое сопротивление'),
        };
    }
}
