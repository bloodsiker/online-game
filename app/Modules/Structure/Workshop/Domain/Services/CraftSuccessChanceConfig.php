<?php

declare(strict_types=1);

namespace App\Modules\Structure\Workshop\Domain\Services;

/**
 * Шанс успеха крафта для профессий из APPLICABLE_SKILL_IDS: не 100%,
 * а BASE_CHANCE в момент, когда навык только дотянул до требования
 * рецепта, растущий линейно до 100% к порогу следующего тира рецептов
 * (см. TIER_THRESHOLDS — общие для всех мирных профессий: 1/50/100/150/200,
 * потолок навыка 300). Остальные профессии (не из списка) крафтят как
 * раньше — гарантированно.
 */
final class CraftSuccessChanceConfig
{
    private const APPLICABLE_SKILL_IDS = [18, 20, 21, 22, 23]; // Алхимик, Ремесленник, Кузнец, Колдун, Ювелир

    private const TIER_THRESHOLDS = [1, 50, 100, 150, 200, 300];

    private const BASE_CHANCE = 50;

    public static function isApplicable(int $skillId): bool
    {
        return in_array($skillId, self::APPLICABLE_SKILL_IDS, true);
    }

    public static function chance(int $skillId, int $requiredLevel, int $currentLevel): int
    {
        if (! self::isApplicable($skillId)) {
            return 100;
        }

        $nextThreshold = self::nextThreshold($requiredLevel);

        if ($nextThreshold <= $requiredLevel) {
            return 100;
        }

        $progress = ($currentLevel - $requiredLevel) / ($nextThreshold - $requiredLevel);
        $chance = self::BASE_CHANCE + $progress * (100 - self::BASE_CHANCE);

        return (int) min(100, max(self::BASE_CHANCE, round($chance)));
    }

    private static function nextThreshold(int $requiredLevel): int
    {
        foreach (self::TIER_THRESHOLDS as $threshold) {
            if ($threshold > $requiredLevel) {
                return $threshold;
            }
        }

        return $requiredLevel;
    }
}
