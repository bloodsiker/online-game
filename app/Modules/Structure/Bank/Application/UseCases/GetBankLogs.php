<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Domain\Models\BankLog;
use Illuminate\Pagination\LengthAwarePaginator;

class GetBankLogs
{
    public function execute(int $userId, int $perPage = 30): LengthAwarePaginator
    {
        return BankLog::with('relatedUser')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}