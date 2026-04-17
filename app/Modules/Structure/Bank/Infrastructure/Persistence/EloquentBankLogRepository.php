<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence;

use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\Structure\Bank\Domain\Models\BankLog;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentBankLogRepository implements BankLogRepository
{
    public function paginateForUser(int $userId, int $perPage = 30): LengthAwarePaginator
    {
        return BankLog::with('relatedUser')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(int $userId, BankAction $action, int $amount, int $balanceAfter, ?int $relatedUserId = null): void
    {
        BankLog::create([
            'user_id' => $userId,
            'related_user_id' => $relatedUserId,
            'action' => $action,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
        ]);
    }
}
