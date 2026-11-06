<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankDepositRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Bank\Domain\Services\BankBalanceLimits;
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
        return $this->transactionManager->run(function () use ($user, $depositId): BankResultDTO {
            // Lock order is user -> deposit in all deposit flows.
            $lockedUser = $this->bankUserRepository->lockForUpdate($user->id);
            $deposit = $this->bankDepositRepository->findOpenForUser($lockedUser->id, $depositId, forUpdate: true);
            if ($deposit === null) {
                return new BankResultDTO(false, 'Вклад не найден.');
            }
            $payout = $deposit->isMatured() ? $deposit->payout() : $deposit->amount;
            if (! BankBalanceLimits::canCreditWallet((int) $lockedUser->money, $payout)) {
                return new BankResultDTO(false, __('bank.wallet_limit'));
            }
            if (! $this->bankDepositRepository->close($deposit)) {
                return new BankResultDTO(false, 'Вклад не найден.');
            }
            $this->bankUserRepository->incrementMoney($lockedUser, $payout);

            return new BankResultDTO(true, sprintf('Вклад закрыт. Вы получили %s монет.', number_format($payout)));
        });
    }
}
