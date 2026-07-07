<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence;

use App\Modules\Structure\Bank\Domain\Contracts\BankDepositRepository;
use App\Modules\Structure\Bank\Domain\Models\BankDeposit;
use Illuminate\Support\Collection;

class EloquentBankDepositRepository implements BankDepositRepository
{
    public function create(int $userId, int $amount, float $percent, int $termDays, \DateTimeInterface $maturesAt): BankDeposit
    {
        return BankDeposit::create([
            'user_id' => $userId,
            'amount' => $amount,
            'percent' => $percent,
            'term_days' => $termDays,
            'matures_at' => $maturesAt,
        ]);
    }

    public function findOpenForUser(int $userId, int $depositId): ?BankDeposit
    {
        return BankDeposit::where('id', $depositId)
            ->where('user_id', $userId)
            ->whereNull('closed_at')
            ->first();
    }

    public function getOpenByUser(int $userId): Collection
    {
        return BankDeposit::where('user_id', $userId)
            ->whereNull('closed_at')
            ->orderBy('matures_at')
            ->get();
    }

    public function getClosedByUser(int $userId, int $limit = 10): Collection
    {
        return BankDeposit::where('user_id', $userId)
            ->whereNotNull('closed_at')
            ->orderByDesc('closed_at')
            ->limit($limit)
            ->get();
    }

    public function close(BankDeposit $deposit): void
    {
        $deposit->closed_at = now();
        $deposit->save();
    }
}