<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Contracts;

use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use Illuminate\Pagination\LengthAwarePaginator;

interface BankLogRepository
{
    public function paginateForUser(int $userId, int $perPage = 30): LengthAwarePaginator;

    public function create(int $userId, BankAction $action, int $amount, int $balanceAfter, ?int $relatedUserId = null): void;
}
