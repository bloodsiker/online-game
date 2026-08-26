<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Contracts;

use App\Modules\Structure\Bank\Domain\Models\BankDeposit;
use Illuminate\Support\Collection;

interface BankDepositRepository
{
    public function create(int $userId, int $amount, float $percent, int $termDays, \DateTimeInterface $maturesAt): BankDeposit;

    public function findOpenForUser(int $userId, int $depositId): ?BankDeposit;

    /** @return Collection<int, BankDeposit> */
    public function getOpenByUser(int $userId): Collection;

    /** @return Collection<int, BankDeposit> */
    public function getClosedByUser(int $userId, int $limit = 10): Collection;

    public function close(BankDeposit $deposit): void;
}
