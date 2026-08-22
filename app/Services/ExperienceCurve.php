<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Формула таблицы опыта по уровням — раньше значения были вручную вбиты в
 * GenerateSeed::createExp() (до 50 уровня, без формулы, шаг рос произвольно).
 * Теперь это функция от уровня.
 *
 * Идея: exp_diff(L) = «опыт с одного убийства типового моба своего уровня»
 *                      × «сколько таких убийств нужно для левелапа».
 * Опыт с моба растёт как level^1.5 (см. MonsterStatFormulas::expReward,
 * тот же коэффициент — единый источник правды).
 *
 * killsPerLevel(L) — три сегмента:
 *   1-20     — быстрый вход для новичка, почти не меняется (15 → 21 убийство);
 *   21-100   — НАМЕРЕННЫЙ СКАЧОК сразу после 20 lvl (21 → 100 убийств за один
 *              уровень), дальше экспоненциальный разгон 100 → 9000. Раньше
 *              разгон стартовал плавно от 21 и к 100 lvl доходил лишь до 1870 —
 *              реальный фарм на «Заросшей дороге» (25-36 lvl) показал прокачку
 *              в 1.44x быстрее, чем предполагала кривая (см. обсуждение
 *              2026-08-14: игрок на 29 lvl почти взял уровень за одну карту).
 *              Явное требование: «до 20 не долго, дальше — не менее 100
 *              убийств на уровень». Скачок специально резкий (не сглаженная
 *              кривая через границу 20/21) — это и есть стена между
 *              «быстрым стартом» и «настоящей игрой».
 *   100-1000 — пологое эндгейм-плато 9000 → 14440 (тот же коэффициент роста
 *             ~1.6x, что и в прежней версии этого сегмента, просто
 *             пересчитан от новой точки старта).
 * За пределами 1000 формула продолжает третий сегмент (то же уравнение,
 * t продолжает расти выше 1) — не обрывается, но нужно калибровать заново,
 * если мир реально дорастёт до таких уровней.
 *
 * experiences.exp/exp_diff и players.exp/exp_up/exp_diff — уже bigint (не
 * int32), переполнение не грозит: cumulative exp на 1000 lvl ~10^11,
 * bigint потолок ~9.2×10^18.
 */
final class ExperienceCurve
{
    private const MONSTER_EXP_COEFFICIENT = 10.0;

    // Сегмент 1: быстрый старт (1-20) — не менялся
    private const EARLY_BASE = 15;

    private const EARLY_GROWTH = 3; // +1 убийство за каждые 3 уровня

    private const EARLY_END_LEVEL = 20;

    // Сегмент 2: разгон (21-100) — старт со скачка, дальше экспонента до «стены»
    private const RAMP_START_LEVEL = 21;

    private const RAMP_START_VALUE = 100.0;

    private const RAMP_END_LEVEL = 100;

    private const RAMP_END_VALUE = 9000.0;

    // Сегмент 3: эндгейм-плато (100-1000) — пологий, но непрерывный рост
    private const ENDGAME_END_LEVEL = 1000;

    private const ENDGAME_END_VALUE = 14440.0;

    /** «Опыт за убийство типового моба своего уровня» — тот же ориентир, что и MonsterStatFormulas::expReward с множителем 1.0. */
    public static function referenceMonsterExp(int $level): int
    {
        return (int) round(self::MONSTER_EXP_COEFFICIENT * $level ** 1.5);
    }

    public static function killsPerLevel(int $level): int
    {
        if ($level <= self::EARLY_END_LEVEL) {
            return self::EARLY_BASE + intdiv($level, self::EARLY_GROWTH);
        }

        if ($level <= self::RAMP_END_LEVEL) {
            return (int) round(self::interpolateExponential(
                $level, self::RAMP_START_LEVEL, self::RAMP_END_LEVEL, self::RAMP_START_VALUE, self::RAMP_END_VALUE
            ));
        }

        return (int) round(self::interpolateExponential(
            $level, self::RAMP_END_LEVEL, self::ENDGAME_END_LEVEL, self::RAMP_END_VALUE, self::ENDGAME_END_VALUE
        ));
    }

    /** Плавная экспоненциальная интерполяция между двумя опорными точками (уровень → значение). */
    private static function interpolateExponential(int $level, int $fromLevel, int $toLevel, float $fromValue, float $toValue): float
    {
        $t = ($level - $fromLevel) / ($toLevel - $fromLevel);

        return $fromValue * ($toValue / $fromValue) ** $t;
    }

    /** Сколько опыта нужно НАБРАТЬ на этом уровне, чтобы перейти на следующий. */
    public static function expDiff(int $level): int
    {
        return self::referenceMonsterExp($level) * self::killsPerLevel($level);
    }

    /**
     * Таблица уровень→[exp, exp_diff] как в experiences.exp/exp_diff:
     * exp — суммарный опыт, накопленный к ДОСТИЖЕНИЮ уровня (0 на 1 уровне),
     * exp_diff — требование для перехода с этого уровня на следующий.
     *
     * @return array<int, array{exp: int, exp_diff: int}>
     */
    public static function table(int $maxLevel): array
    {
        $rows = [];
        $cumulative = 0;

        for ($level = 1; $level <= $maxLevel; $level++) {
            $diff = self::expDiff($level);
            $rows[$level] = ['exp' => $cumulative, 'exp_diff' => $diff];
            $cumulative += $diff;
        }

        return $rows;
    }
}
