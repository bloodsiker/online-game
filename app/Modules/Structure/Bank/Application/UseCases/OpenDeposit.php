<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankDepositRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Bank\Domain\Services\DepositTerms;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;

class OpenDeposit
{
    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankDepositRepository $bankDepositRepository,
        private readonly DepositTerms $depositTerms,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $user, int $amount, int $termDays): BankResultDTO
    {
        $term = $this->depositTerms->termFor($termDays);
        if ($term === null) {
            return new BankResultDTO(false, 'Неверный срок вклада.');
        }

        if ($amount < $term['min'] || $amount > $term['max']) {
            return new BankResultDTO(false, sprintf('Сумма вклада должна быть от %s до %s монет.', number_format($term['min']), number_format($term['max'])));
        }

        return $this->transactionManager->run(function () use ($user, $amount, $term, $termDays): BankResultDTO {
            $lockedUser = $this->bankUserRepository->lockForUpdate($user->id);
            if ($amount > $lockedUser->money) {
                return new BankResultDTO(false, 'Сумма превышает количество монет в кошельке.');
            }
            $openDeposits = $this->bankDepositRepository->getOpenByUser($lockedUser->id);
            if ($openDeposits->contains(fn ($deposit) => (int) $deposit->term_days === $termDays)) {
                return new BankResultDTO(false, 'У вас уже есть такой депозит!');
            }
            if (! $this->bankUserRepository->decrementMoney($lockedUser, $amount)) {
                return new BankResultDTO(false, 'Сумма превышает количество монет в кошельке.');
            }
            $this->bankDepositRepository->create(
                userId: $lockedUser->id,
                amount: $amount,
                percent: $term['percent'],
                termDays: $termDays,
                maturesAt: Carbon::now()->addDays($termDays),
            );

            return new BankResultDTO(true, sprintf('Вклад «%s» на %s монет открыт.', $term['label'], number_format($amount)));
        });
    }
}
