<?php

namespace App\Enums;

enum ItemEffectType: string
{
    case HEAL_HP = 'heal_hp';
    case HEAL_MP = 'heal_mp';

    case BUFF_ATTACK = 'buff_attack';
    case BUFF_DEFENSE = 'buff_armor';

    case DAMAGE_HP = 'damage_hp';

    public function isInstant(): bool
    {
        return match ($this) {
            self::HEAL_HP, self::HEAL_MP, self::DAMAGE_HP => true,

            default => false,
        };
    }

    public function isTimed(): bool
    {
        return !$this->isInstant();
    }
}
