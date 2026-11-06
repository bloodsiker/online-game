<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

use App\Modules\Effect\Domain\Enums\ActiveEffectType;

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
    case CHANGE_AVATAR = 'change_avatar';
    case GRANT_DIAMONDS = 'grant_diamonds';
    // Противоядия: снимают дебафф своего типа; value — лечение HP в %,
    // duration_seconds — иммунитет к повторному наложению этого дебаффа.
    case CURE_POISON = 'cure_poison';
    case CURE_BLEED = 'cure_bleed';
    case CURE_BURN = 'cure_burn';
    case CURE_PARALYSIS = 'cure_paralysis';

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
            self::CHANGE_AVATAR => __('Смена аватара'),
            self::GRANT_DIAMONDS => __('Начисление бриллиантов'),
            self::CURE_POISON => __('Снятие отравления'),
            self::CURE_BLEED => __('Снятие кровотечения'),
            self::CURE_BURN => __('Снятие горения'),
            self::CURE_PARALYSIS => __('Снятие паралича'),
        };
    }

    public function isInstant(): bool
    {
        return match ($this) {
            self::HEAL_HP, self::HEAL_MP, self::DAMAGE_HP, self::RESTORE_LOST_EXP, self::RESPEC_STATS, self::GRANT_DIAMONDS,
            self::CURE_POISON, self::CURE_BLEED, self::CURE_BURN, self::CURE_PARALYSIS => true,
            default => false,
        };
    }

    /** Какой дебафф снимает противоядие (null — эффект не противоядие). */
    public function curedEffectType(): ?ActiveEffectType
    {
        return match ($this) {
            self::CURE_POISON => ActiveEffectType::POISON,
            self::CURE_BLEED => ActiveEffectType::BLEED,
            self::CURE_BURN => ActiveEffectType::BURN,
            self::CURE_PARALYSIS => ActiveEffectType::PARALYSIS,
            default => null,
        };
    }

    public function isTimed(): bool
    {
        return ! $this->isInstant();
    }
}
