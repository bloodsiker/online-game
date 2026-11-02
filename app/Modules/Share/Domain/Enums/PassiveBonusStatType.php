<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

/**
 * Словарь характеристик для пассивных бонусов (JSON-массивы вида
 * [{"type":..., "value":..., "is_percent":...}]) — общий для нескольких
 * независимых потребителей: клановые навыки (clan_skill_definitions),
 * пассивные магические скиллы (magic_skills.effects / «Пассивные бонусы»
 * в админке) и связанные Effect-записи (effects.stat_modifiers). Раньше
 * назывался ClanSkillEffectType и жил в Clan-модуле — название вводило в
 * заблуждение, когда тот же словарь стали применять за пределами кланов.
 */
enum PassiveBonusStatType: string
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
    case MAGIC_DAMAGE_PERCENT = 'magic_damage_percent';
    case DEBUFF_DURATION_REDUCTION = 'debuff_duration_reduction';
    case LETHAL_SURVIVE_CHANCE = 'lethal_survive_chance';

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
            self::MAGIC_DAMAGE_PERCENT => __('Магический урон (%, значение = сам процент, is_percent не ставить)'),
            self::DEBUFF_DURATION_REDUCTION => __('Сокращение длительности дебаффов (%, боевой хук, не StatSheet)'),
            self::LETHAL_SURVIVE_CHANCE => __('Шанс выжить при смертельном ударе (%, боевой хук, не StatSheet)'),
        };
    }
}
