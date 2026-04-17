<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class Withdraw
{
    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankLogRepository $bankLogRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $user, int $amount): BankResultDTO
    {
        if ($amount > $user->bank_balance) {
            return new BankResultDTO(false, 'Сумма превышает баланс банковского счёта.');
        }

        $this->transactionManager->run(function () use ($user, $amount) {
            $this->bankUserRepository->decrementBankBalance($user, $amount);
            $this->bankUserRepository->incrementMoney($user, $amount);

            $balanceAfter = $this->bankUserRepository->refresh($user)->bank_balance;

            $this->bankLogRepository->create(
                userId: $user->id,
                action: BankAction::WITHDRAW,
                amount: $amount,
                balanceAfter: $balanceAfter,
            );
        });

        return new BankResultDTO(true, sprintf('Вы сняли %s монет из банка.', number_format($amount)));
    }
}
