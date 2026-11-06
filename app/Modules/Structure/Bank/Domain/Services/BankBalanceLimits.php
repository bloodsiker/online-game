<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Services;

use App\Modules\User\Domain\Services\WalletBalanceLimits;

final class BankBalanceLimits
{
    // users.money is a signed MySQL INT; account balances must also fit PHP integer arithmetic.
    public const MAX_WALLET = WalletBalanceLimits::MAX_BALANCE;

    public static function canCreditWallet(int $balance, int $amount): bool
    {
        return WalletBalanceLimits::canCredit($balance, $amount);
    }

    public static function canCreditAccount(int $balance, int $amount): bool
    {
        return $amount > 0 && $balance <= PHP_INT_MAX - $amount;
    }
}
