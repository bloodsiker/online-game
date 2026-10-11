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
     * ИСТОРИЯ: раньше доля денег с монстра «сбрасывалась» на 100% в начале
     * каждого тира (20, 50 lvl) — см. git-историю и docs/economy-pricing-*.md.
     * Проблема была реальной (ExperienceCurve::killsPerLevel() взрывается
     * экспоненциально — 21→100→~9000 килов к 100 lvl, без сдерживания доход
     * рос в сотни раз внутри тира), но сброс на границе тира создавал ОБРАТНУЮ
     * проблему: деньги с монстра резко ПАДАЛИ весь тир, а на границе следующего
     * тира внезапно ПОДСКАКИВАЛИ обратно (лвл49 94-187 → лвл50 530-1061) —
     * не «плавный рост», а зигзаг.
     *
     * ТЕКУЩЕЕ РЕШЕНИЕ (по прямому указанию пользователя): деньги с монстра
     * растут МОНОТОННО на каждом уровне (никогда не меньше, чем у монстра
     * более низкого уровня) степенной кривой от уровня — `(level/20)^p` —
     * без каких-либо сбросов на границах тиров. Показатель степени p<1 даёт
     * ту же форму, что уже используется в ExperienceCurve::referenceMonsterExp
     * (сам exp растёт на ~7.6% на 21 lvl и всего на ~1.7% на 90 lvl — рост
     * есть всегда, но процент прироста сам плавно уменьшается) — деньги
     * наследуют эту же форму вместо резких искусственных скачков.
     *
     * MONEY_GROWTH_EXPONENT подобран бинарным поиском так, чтобы суммарный
     * накопленный доход игрока за весь диапазон 20-89 lvl (сумма
     * killsPerLevel(lvl) × money(lvl) по всем уровням) равнялся ~30 000 000 —
     * целевая цифра, заданная пользователем как компромисс между «деньги не
     * падают» и «экономика не взрывается» (для сравнения: без всякого
     * сдерживания эта же сумма получилась бы ~122 000 000).
     *
     * $difficultyRatio сохраняет пропорциональность деньгам от `exp` для
     * мобов с expMultiplier ≠ 1.0 (боссы/элиты) — обычный моб (multiplier=1)
     * получает ровно точку на степенной кривой, более «жирный» по exp моб
     * получает пропорционально больше денег.
     */
    private const MONEY_GROWTH_REFERENCE_LEVEL = 20;

    private const MONEY_GROWTH_EXPONENT = 0.4363;

    private const MONEY_MIN_PERCENT_OF_EXP = 0.15;

    private const MONEY_MAX_PERCENT_OF_EXP = 0.30;

    /** @return array{0: int, 1: int} [min, max] денег с монстра — монотонно растущая степенная кривая от уровня, без сбросов на границах тиров (см. докблок выше) */
    public static function moneyRange(int $exp, int $level = 1): array
    {
        if ($level < self::MONEY_GROWTH_REFERENCE_LEVEL) {
            return [
                (int) round($exp * self::MONEY_MIN_PERCENT_OF_EXP),
                (int) round($exp * self::MONEY_MAX_PERCENT_OF_EXP),
            ];
        }

        $referenceExp = ExperienceCurve::referenceMonsterExp(self::MONEY_GROWTH_REFERENCE_LEVEL);
        $typicalExpAtLevel = ExperienceCurve::referenceMonsterExp($level);
        $difficultyRatio = $exp / max(1, $typicalExpAtLevel);
        $growthScale = ($level / self::MONEY_GROWTH_REFERENCE_LEVEL) ** self::MONEY_GROWTH_EXPONENT;

        return [
            (int) round($referenceExp * self::MONEY_MIN_PERCENT_OF_EXP * $growthScale * $difficultyRatio),
            (int) round($referenceExp * self::MONEY_MAX_PERCENT_OF_EXP * $growthScale * $difficultyRatio),
        ];
    }
}
