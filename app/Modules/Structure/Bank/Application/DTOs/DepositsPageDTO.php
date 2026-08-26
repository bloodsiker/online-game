<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\DTOs;

use App\Modules\Structure\Bank\Domain\Models\BankDeposit;
use Illuminate\Support\Collection;

class DepositsPageDTO
{
    /**
     * @param  array<int, array{label: string, percent: float, min: int, max: int}>  $terms  срок в днях => условия
     * @param  Collection<int, BankDeposit>  $openDeposits
     */
    public function __construct(
        public readonly int $money,
        public readonly array $terms,
        public readonly Collection $openDeposits,
    ) {}

    public function hasDepositFor(int $termDays): bool
    {
        return $this->openDeposits->contains(fn ($deposit) => $deposit->term_days === $termDays);
    }
}
