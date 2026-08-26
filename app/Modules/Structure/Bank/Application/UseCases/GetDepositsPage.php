<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\DepositsPageDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankDepositRepository;
use App\Modules\Structure\Bank\Domain\Services\DepositTerms;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetDepositsPage
{
    public function __construct(
        private readonly BankDepositRepository $bankDepositRepository,
        private readonly DepositTerms $depositTerms,
    ) {}

    public function execute(User $user): DepositsPageDTO
    {
        return new DepositsPageDTO(
            money: $user->money,
            terms: $this->depositTerms->all(),
            openDeposits: $this->bankDepositRepository->getOpenByUser($user->id),
        );
    }
}
