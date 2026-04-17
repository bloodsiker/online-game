<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Models\User;
use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;

class Deposit
{
    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankLogRepository $bankLogRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $user, int $amount): BankResultDTO
    {
        if ($amount > $user->money) {
            return new BankResultDTO(false, 'Сумма превышает количество монет в кошельке.');
        }

        $this->transactionManager->run(function () use ($user, $amount) {
            $this->bankUserRepository->decrementMoney($user, $amount);
            $this->bankUserRepository->incrementBankBalance($user, $amount);

            $balanceAfter = $this->bankUserRepository->refresh($user)->bank_balance;

            $this->bankLogRepository->create(
                userId: $user->id,
                action: BankAction::DEPOSIT,
                amount: $amount,
                balanceAfter: $balanceAfter,
            );
        });

        return new BankResultDTO(true, sprintf('Вы положили %s монет в банк.', number_format($amount)));
    }
}
