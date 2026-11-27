<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Effect\Domain\Enums\EffectDamageScalingType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;

/**
 * Урон за тик периодического эффекта заклинания игрока (ожог, яд, кровотечение).
 *
 * Эффект наносит за всю длительность power_percent % от урона удара (basis «hit»)
 * либо от максимального HP цели (basis «target_max_hp»), поровну по тикам.
 */
final readonly class SpellDotCalculator
{
    public const BASIS_HIT = 'hit';

    public const BASIS_TARGET_MAX_HP = 'target_max_hp';

    /** Если процент в заклинании не задан: доля урона удара / доля макс. HP цели за всю длительность. */
    private const DEFAULT_HIT_PERCENT = 50.0;

    private const DEFAULT_MAX_HP_PERCENT = 2.0;

    /** @return int|null null — эффект не периодический (его значение не пересчитываем) */
    public function tickValue(
        Effect $effect,
        ?float $powerPercent,
        ?string $powerBasis,
        int $hitDamage,
        int $targetMaxHp,
        int $durationSeconds,
    ): ?int {
        if (! ($effect->resolvedActiveType()?->isDoT() ?? false)) {
            return null;
        }

        $basis = in_array($powerBasis, [self::BASIS_HIT, self::BASIS_TARGET_MAX_HP], true)
            ? $powerBasis
            : ($effect->resolvedDamageScalingType() === EffectDamageScalingType::TARGET_MAX_HP
                ? self::BASIS_TARGET_MAX_HP
                : self::BASIS_HIT);

        $percent = max(0.0, $powerPercent ?? ($basis === self::BASIS_HIT ? self::DEFAULT_HIT_PERCENT : self::DEFAULT_MAX_HP_PERCENT));
        $totalTicks = max(1, intdiv(max(0, $durationSeconds), max(1, (int) $effect->tick_interval)));
        $total = ($basis === self::BASIS_HIT ? $hitDamage : $targetMaxHp) * $percent / 100;

        return $total > 0 ? max(1, (int) round($total / $totalTicks)) : 0;
    }
}
