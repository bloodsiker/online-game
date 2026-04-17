<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\DTOs;

final readonly class BankPageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $logs
     * @param  array{hasPages: bool, currentPage: int, lastPage: int, pageFrom: int, pageTo: int, urls: array<int, string>}  $pagination
     */
    public function __construct(
        public string $bankAccount,
        public int $money,
        public int $bankBalance,
        public array $logs,
        public array $pagination,
    ) {}
}
