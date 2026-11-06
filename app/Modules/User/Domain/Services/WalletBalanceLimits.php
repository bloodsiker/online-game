<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Services;

final class WalletBalanceLimits
{
    public const MAX_BALANCE = 2147483647;

    public static function canCredit(int $balance, int $amount): bool
    {
        return $amount > 0 && $balance <= self::MAX_BALANCE - $amount;
    }
}
