<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

final class ArtifactUpgradeRules
{
    public const MAX_LEVEL = 25;

    public const BONUS_PER_LEVEL = 0.05;

    public static function bonus(float $baseValue, int $level): float
    {
        return $baseValue * max(0, $level) * self::BONUS_PER_LEVEL;
    }
}
