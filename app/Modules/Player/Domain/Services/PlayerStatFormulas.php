<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

final class PlayerStatFormulas
{
    public const DEFAULT_HP = 10;

    public const DEFAULT_MP = 10;

    /**
     * HP растёт от уровня, а не от силы: HP-монополия силы ломала
     * треугольник танк/уворот/крит (сила давала бы HP+броню+урон разом).
     */
    public const HP_PER_LEVEL = 12;

    /**
     * Выносливость — отдельная стата, отвечающая только за HP поверх уровневой
     * базы: осознанный трейдофф «живучесть vs сила класса», а не побочный
     * эффект прокачки боевой статы (см. HP_PER_LEVEL).
     */
    public const HP_PER_ENDURANCE = 3;

    public const MP_PER_MUD = 3;

    public const DODGE_PER_AGILITY = 1;

    public const CRITICAL_PER_INT = 1;

    public const ARMOR_PER_STR = 1;

    /** Магическое сопротивление от Мудрости — та же роль, что у Силы для брони, но по другой стате */
    public const MAGIC_RESIST_PER_WIS = 1;

    /** Прибавка к урону оружия за очко силы, % (80 силы → +20% урона) */
    public const DMG_PCT_PER_STR = 0.35;

    /**
     * Мягкий потолок бонуса урона от силы, %: рост асимптотически ограничен,
     * иначе на высоких уровнях сила давала бы сотни процентов урона при
     * капнутых шансах уворота и критурона у других классов.
     */
    public const DMG_PCT_CAP = 100.0;

    /** Базовый множитель критического урона, % */
    public const CRIT_DAMAGE_BASE = 175;

    /** Рост множителя критического урона за очко интуиции, % */
    public const CRIT_DAMAGE_PER_INT = 1;

    /** Софткап множителя крита: рост выше CRIT_DAMAGE_BASE асимптотически ограничен этим значением */
    public const CRIT_DAMAGE_CAP = 300.0;

    /** Шанс маг-крита растёт 1:1 до этого значения (% = сырая стата) */
    public const MAGIC_CRIT_CHANCE_BASE = 30.0;

    /**
     * Софткап шанса маг-крита: выше MAGIC_CRIT_CHANCE_BASE рост асимптотически
     * ограничен этим значением — тот же потолок 75%, что у физического крита
     * (HitCalculator::statChance(), BASE_CHANCE+MAX_STAT_BONUS), для паритета
     * между классами.
     */
    public const MAGIC_CRIT_CHANCE_CAP = 75.0;

    /**
     * Мягкий потолок вклада интеллекта в силу магии (эталон на level/12=1),
     * та же роль, что у DMG_PCT_CAP для силы: без него чистый стат-стак в
     * интеллект давал бы неограниченный урон, никак не завязанный на
     * magic-снаряжение (у физики аналог — урон оружия, целиком от предмета).
     * magic_attack с предметов в этот кап НЕ входит — растёт отдельно и без
     * ограничения, как и раньше (см. MagicHitCalculator::magicPower()).
     */
    public const INTELLIGENCE_MAGIC_POWER_CAP = 200.0;

    /**
     * Бонус урона от силы, нормированный по уровню: «типичный танк» любого
     * уровня получает одинаковый процент (иначе линейный рост стат к 500 lvl
     * раздувал бы множитель), плюс мягкий потолок как страховка.
     */
    public static function strengthDamagePercent(float $strength, int $level): float
    {
        $levelScale = max(1.0, $level / 12);
        $raw = max(0, ($strength - 1) * self::DMG_PCT_PER_STR) / $levelScale;

        return self::DMG_PCT_CAP * $raw / ($raw + self::DMG_PCT_CAP);
    }

    /**
     * Мягкий потолок вклада интеллекта в magicPower — та же схема, что у
     * strengthDamagePercent (нормировка по уровню, затем асимптотический
     * кап), только тут кап применяется к самому интеллекту, а не к готовому
     * проценту: magicPower — аддитивная величина (intelligence + magic_attack),
     * не множитель на урон оружия, поэтому капать нужно вход, а не выход.
     */
    public static function cappedIntelligence(float $intelligence, int $level): float
    {
        $levelScale = max(1.0, $level / 12);
        $raw = max(0, $intelligence - 1) / $levelScale;
        $cappedPercent = self::INTELLIGENCE_MAGIC_POWER_CAP * $raw / ($raw + self::INTELLIGENCE_MAGIC_POWER_CAP);

        return $cappedPercent * $levelScale;
    }

    /**
     * Рост критурона от интуиции, нормированный по уровню — «типичный крит»
     * любого уровня получает одинаковую прибавку (жёсткий потолок роста
     * дополнительно страхует софткап в HitCalculator).
     */
    public static function critDamageBonus(float $intuition, int $level): float
    {
        $levelScale = max(1.0, $level / 12);

        return max(0, ($intuition - 1) * self::CRIT_DAMAGE_PER_INT) / $levelScale;
    }

    /**
     * Итоговый множитель крита с мягким потолком: до CRIT_DAMAGE_BASE растёт
     * линейно, дальше — асимптотически к CRIT_DAMAGE_CAP. Общая формула для
     * физического (HitCalculator, «сырое» значение растёт от Интуиции) и
     * магического (MagicHitCalculator, «сырое» значение — целиком с
     * экипировки, см. ShareItemStatType::MAGIC_CRIT_DAMAGE) крита.
     */
    public static function effectiveCritDamage(int $rawCritDamage): float
    {
        $raw = (float) $rawCritDamage;

        if ($raw <= self::CRIT_DAMAGE_BASE) {
            return $raw;
        }

        $growth = $raw - self::CRIT_DAMAGE_BASE;
        $span = self::CRIT_DAMAGE_CAP - self::CRIT_DAMAGE_BASE;

        return self::CRIT_DAMAGE_BASE + $span * $growth / ($growth + $span);
    }

    /**
     * Шанс маг-крита с мягким потолком — та же асимптотическая форма, что у
     * effectiveCritDamage(), только для процента шанса (0-100), а не
     * множителя урона. У магического крита нет встречной статы защитника
     * (в отличие от физического HitCalculator::statChance()), поэтому кап
     * применяется прямо к сырому значению атакующего, без противостояния.
     */
    public static function effectiveMagicCriticalChance(int $rawChance): float
    {
        $raw = (float) $rawChance;

        if ($raw <= self::MAGIC_CRIT_CHANCE_BASE) {
            return max(0.0, $raw);
        }

        $growth = $raw - self::MAGIC_CRIT_CHANCE_BASE;
        $span = self::MAGIC_CRIT_CHANCE_CAP - self::MAGIC_CRIT_CHANCE_BASE;

        return self::MAGIC_CRIT_CHANCE_BASE + $span * $growth / ($growth + $span);
    }
}
