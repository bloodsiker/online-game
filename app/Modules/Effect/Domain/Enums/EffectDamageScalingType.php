<?php

declare(strict_types=1);

namespace App\Modules\Effect\Domain\Enums;

enum EffectDamageScalingType: string
{
    /** Процент от фактически нанесённого удара за каждый тик. */
    case HIT_DAMAGE = 'hit_damage';

    /** Процент от максимального HP цели за всю длительность эффекта. */
    case TARGET_MAX_HP = 'target_max_hp';

    /** Effect::value_per_tick без дополнительного масштабирования. */
    case FIXED = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::HIT_DAMAGE => 'От урона удара',
            self::TARGET_MAX_HP => 'От макс. HP цели',
            self::FIXED => 'Фиксированное значение',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::HIT_DAMAGE => 'Сила (%) берётся от нанесённого удара и применяется на каждом тике.',
            self::TARGET_MAX_HP => 'Сила (%) задаёт общий урон за всю длительность эффекта от максимального HP цели.',
            self::FIXED => 'На каждом тике используется поле «Значение за тик» из эффекта.',
        };
    }
}
