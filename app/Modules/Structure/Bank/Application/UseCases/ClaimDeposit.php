<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankDepositRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Contracts\TransactionManager;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class ClaimDeposit
{
    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankDepositRepository $bankDepositRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $user, int $depositId): BankResultDTO
    {
        $deposit = $this->bankDepositRepository->findOpenForUser($user->id, $depositId);

        if ($deposit === null) {
            return new BankResultDTO(false, 'Вклад не найден.');
        }

        // Досрочное закрытие возвращает только сумму вклада без процентов
        $payout = $deposit->isMatured() ? $deposit->payout() : $deposit->amount;

        $this->transactionManager->run(function () use ($user, $deposit, $payout) {
            $this->bankDepositRepository->close($deposit);
            $this->bankUserRepository->incrementMoney($user, $payout);
        });

        return new BankResultDTO(true, sprintf('Вклад закрыт. Вы получили %s монет.', number_format($payout)));
    }
}