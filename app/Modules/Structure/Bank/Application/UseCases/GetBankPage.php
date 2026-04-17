<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Models\User;
use App\Modules\Structure\Bank\Application\DTOs\BankPageDTO;
use App\Modules\Structure\Bank\Application\Mappers\BankPageViewMapper;
use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;

class GetBankPage
{
    public function __construct(
        private readonly BankLogRepository $bankLogRepository,
        private readonly BankPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $perPage = 30): BankPageDTO
    {
        return $this->mapper->map($user, $this->bankLogRepository->paginateForUser($user->id, $perPage));
    }
}
