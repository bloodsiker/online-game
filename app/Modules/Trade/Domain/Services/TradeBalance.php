<?php

declare(strict_types=1);

namespace App\Modules\Trade\Domain\Services;

final class TradeBalance
{
    public const SKILL_NAME = 'Вольный торг';

    public static function capacity(int $level): int
    {
        return 10 + intdiv(max(1, min(300, $level)), 15);
    }

    public static function salePrice(int $purchasePrice, int $offerPrice, int $level): int
    {
        $level = max(1, min(300, $level));

        // Integer arithmetic makes unit prices stable across split sales.
        return $purchasePrice + intdiv(
            max(0, $offerPrice - $purchasePrice) * (299 + 2 * ($level - 1)),
            299,
        );
    }

    public static function levelThreshold(int $level): int
    {
        return 100 * $level + intdiv(5 * $level * ($level - 1), 2);
    }
}
