<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\Services;

use App\Modules\Player\Domain\Services\ExperienceCurve;

/**
 * Формулы характеристик монстров по уровню — чтобы новый контент не собирался
 * на глаз, а ложился на ту же кривую, что и HitCalculator (см. /admin/docs/battle).
 *
 * Зеркалит константы HitCalculator (REFERENCE_LEVEL=12, ARMOR_CONSTANT=220,
 * SOFTCAP_K=55, BASE_CHANCE=5, MAX_STAT_BONUS=70), которые сами имеют пол
 * levelScale = max(1, level/12) — то есть на уровнях 1-12 (весь диапазон
 * нынешних стартовых мобов) константы не уменьшаются, раздача сырых статов
 * под целевой % шанса/митигации одинакова независимо от уровня внутри этого
 * диапазона. При выходе за 12 уровень сырые статы для того же % начнут расти
 * пропорционально levelScale — это осознанно и должно учитываться при добавлении
 * мобов выше 12 уровня.
 */
final class MonsterStatFormulas
{
    private const REFERENCE_LEVEL = 12.0;

    private const ARMOR_CONSTANT = 220.0;

    private const SOFTCAP_K = 55.0;

    private const BASE_CHANCE = 5.0;

    private const MAX_STAT_BONUS = 70.0;

    /** Базовый HP на уровень до модификатора вида (тот же наклон, что у HP_PER_LEVEL игрока, но ниже). */
    private const HP_BASE = 10.0;

    private const HP_PER_LEVEL = 8.0;

    private static function levelScale(int $level): float
    {
        return max(1.0, $level / self::REFERENCE_LEVEL);
    }

    /**
     * HP игрока по формуле из PlayerStatFormulas (DEFAULT_HP=10, HP_PER_LEVEL=12),
     * без учёта Выносливости — нужен как ориентир для калибровки урона монстра.
     */
    public static function referencePlayerHp(int $level): float
    {
        return 10 + 12 * ($level - 1);
    }

    public static function hp(int $level, float $hpMultiplier): int
    {
        return (int) round((self::HP_BASE + self::HP_PER_LEVEL * ($level - 1)) * $hpMultiplier);
    }

    /** @param  float  $targetMitigation  доля урона поглощаемая бронёй, 0..1 (0.12 = 12%) */
    public static function armorForMitigation(int $level, float $targetMitigation): int
    {
        if ($targetMitigation <= 0.0) {
            return 0;
        }

        $armorConstant = self::ARMOR_CONSTANT * self::levelScale($level);

        return (int) round($armorConstant * $targetMitigation / (1 - $targetMitigation));
    }

    /** @param  float  $targetChancePercent  целевой % шанса (уворота/крита), напр. 14.0 */
    public static function rawStatForChance(int $level, float $targetChancePercent): int
    {
        if ($targetChancePercent <= self::BASE_CHANCE) {
            return 0;
        }

        $fraction = ($targetChancePercent - self::BASE_CHANCE) / self::MAX_STAT_BONUS;
        $softCap = self::SOFTCAP_K * self::levelScale($level);

        return (int) round($softCap * $fraction / (1 - $fraction));
    }

    /**
     * Урон монстра как % от условного HP игрока того же уровня (без Выносливости).
     *
     * @return array{0: int, 1: int} [min, max]
     */
    public static function damageRange(int $level, float $percentOfPlayerHp): array
    {
        $avg = $percentOfPlayerHp / 100 * self::referencePlayerHp($level);
        $min = max(1, (int) round($avg * 0.7));
        $max = max($min + 1, (int) round($avg * 1.3));

        return [$min, $max];
    }

    /**
     * Опыт за полное убийство монстра того же уровня в одиночку (см. AttackService::calculateExperience —
     * при levelDifference=0 множитель=1, и сумма exp за раунды точно равна этому числу).
     *
     * Коэффициент общий с таблицей опыта (см. ExperienceCurve::referenceMonsterExp) —
     * один источник правды, чтобы кривая опыта и опыт мобов не расходились.
     */
    public static function expReward(int $level, float $difficultyMultiplier): int
    {
        return (int) round(ExperienceCurve::referenceMonsterExp($level) * $difficultyMultiplier);
    }

    /**
     * Затухание доли денег с монстра, СБРАСЫВАЕТСЯ на 100% в начале каждого тира
     * (см. docs/economy-pricing-tier1-tier2.md, docs/economy-pricing-tier3.md):
     * ExperienceCurve::killsPerLevel() намеренно взрывается внутри каждого тира
     * (21→100→экспоненциально до 9000 килов к 100 lvl) — без затухания суммарный
     * доход на верхнем чекпоинте тира оказывается в сотни раз больше дохода на
     * нижнем, что при цене «150% от накопленного дохода с начала тира» даёт либо
     * смешные, либо многомиллионные числа. Коэффициент каждого тира подобран
     * бинарным поиском под свой целевой разрыв (Тир2: 933x→300x, Тир3: 308x→40x).
     * Затухание СБРАСЫВАЕТСЯ на границе, а не продолжается глобально — иначе на
     * Тир3 (50-89) множитель от Тир2-коэффициента упал бы до ~1.6% к 90 lvl.
     * Тир1 (1-19) не затрагивается — там экономика уже откалибрована без затухания.
     *
     * @var array<int, float> нижняя граница тира (уровень) => коэффициент затухания за уровень
     */
    private const MONEY_DECAY_BY_TIER_START = [
        20 => 0.9429,
        50 => 0.9220,
    ];

    /** @return array{0: int, 1: int} [min, max] денег с монстра, как доля от опыта (с затуханием доли, сброс на границе тира — см. MONEY_DECAY_BY_TIER_START) */
    public static function moneyRange(int $exp, int $level = 1): array
    {
        $tierStart = 1;
        $decayPerLevel = 1.0;

        foreach (self::MONEY_DECAY_BY_TIER_START as $start => $decay) {
            if ($level >= $start) {
                $tierStart = $start;
                $decayPerLevel = $decay;
            }
        }

        $multiplier = $decayPerLevel ** ($level - $tierStart);

        return [
            (int) round($exp * 0.15 * $multiplier),
            (int) round($exp * 0.30 * $multiplier),
        ];
    }
}
