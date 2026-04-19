<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ItemEffectType: string
{
    case HEAL_HP = 'heal_hp';
    case HEAL_MP = 'heal_mp';
    case BUFF_ATTACK = 'buff_attack';
    case BUFF_DEFENSE = 'buff_armor';
    case DAMAGE_HP = 'damage_hp';

    public function label(): string
    {
        return match ($this) {
            self::HEAL_HP => 'Восстановление HP',
            self::HEAL_MP => 'Восстановление MP',
            self::BUFF_ATTACK => 'Бафф атаки',
            self::BUFF_DEFENSE => 'Бафф защиты',
            self::DAMAGE_HP => 'Урон',
        };
    }

    public function isInstant(): bool
    {
        return match ($this) {
            self::HEAL_HP, self::HEAL_MP, self::DAMAGE_HP => true,
            default => false,
        };
    }

    public function isTimed(): bool
    {
        return ! $this->isInstant();
    }
}
