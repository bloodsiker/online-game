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
    case RESTORE_LOST_EXP = 'restore_lost_exp';
    case RESPEC_STATS = 'respec_stats';
    case RENAME_NAME = 'rename_name';
    case CHANGE_RACE = 'change_race';

    public function label(): string
    {
        return match ($this) {
            self::HEAL_HP => __('Восстановление HP'),
            self::HEAL_MP => __('Восстановление MP'),
            self::BUFF_ATTACK => __('Бафф атаки'),
            self::BUFF_DEFENSE => __('Бафф защиты'),
            self::DAMAGE_HP => __('Урон'),
            self::RESTORE_LOST_EXP => __('Возврат потерянного при смерти опыта'),
            self::RESPEC_STATS => __('Сброс вложенных очков характеристик'),
            self::RENAME_NAME => __('Смена игрового имени'),
            self::CHANGE_RACE => __('Смена расы'),
        };
    }

    public function isInstant(): bool
    {
        return match ($this) {
            self::HEAL_HP, self::HEAL_MP, self::DAMAGE_HP, self::RESTORE_LOST_EXP, self::RESPEC_STATS => true,
            default => false,
        };
    }

    public function isTimed(): bool
    {
        return ! $this->isInstant();
    }
}
