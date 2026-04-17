<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class GetBankLogs
{
    public function __construct(
        private readonly BankLogRepository $bankLogRepository,
    ) {}

    public function execute(int $userId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->bankLogRepository->paginateForUser($userId, $perPage);
    }
}
