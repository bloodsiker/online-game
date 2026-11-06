<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\Structure\Bank\Domain\Services\BankBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class Deposit
{
    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankLogRepository $bankLogRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $user, int $amount): BankResultDTO
    {
        if ($amount <= 0) {
            return new BankResultDTO(false, __('bank.invalid_amount'));
        }

        return $this->transactionManager->run(function () use ($user, $amount): BankResultDTO {
            $lockedUser = $this->bankUserRepository->lockForUpdate($user->id);
            if ($amount > $lockedUser->money) {
                return new BankResultDTO(false, 'Сумма превышает количество монет в кошельке.');
            }
            if (! BankBalanceLimits::canCreditAccount((int) $lockedUser->bank_balance, $amount)) {
                return new BankResultDTO(false, __('bank.account_limit'));
            }
            if (! $this->bankUserRepository->decrementMoney($lockedUser, $amount)) {
                return new BankResultDTO(false, 'Сумма превышает количество монет в кошельке.');
            }
            $this->bankUserRepository->incrementBankBalance($lockedUser, $amount);
            $balanceAfter = $this->bankUserRepository->refresh($lockedUser)->bank_balance;
            $this->bankLogRepository->create(
                userId: $lockedUser->id,
                action: BankAction::DEPOSIT,
                amount: $amount,
                balanceAfter: $balanceAfter,
            );

            return new BankResultDTO(true, sprintf('Вы положили %s монет в банк.', number_format($amount)));
        });
    }
}
