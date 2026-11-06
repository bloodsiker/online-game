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

class Transfer
{
    public const COMMISSION_PERCENT = 1;

    public const COMMISSION = self::COMMISSION_PERCENT / 100;

    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankLogRepository $bankLogRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $sender, string $account, int $amount): BankResultDTO
    {
        if ($amount <= 0) {
            return new BankResultDTO(false, __('bank.invalid_amount'));
        }
        $recipient = $this->bankUserRepository->findByBankAccount($account);
        if ($recipient === null) {
            return new BankResultDTO(false, 'Счёт не найден.');
        }
        if ((int) $recipient->id === (int) $sender->id) {
            return new BankResultDTO(false, 'Нельзя переводить самому себе.');
        }
        // Exact integer equivalent of ceil(amount * 1%), without float rounding/overflow.
        $commission = intdiv($amount, 100) * self::COMMISSION_PERCENT
            + intdiv(($amount % 100) * self::COMMISSION_PERCENT + 99, 100);
        if ($amount > PHP_INT_MAX - $commission) {
            return new BankResultDTO(false, __('bank.invalid_amount'));
        }
        $total = $amount + $commission;

        return $this->transactionManager->run(function () use ($sender, $recipient, $account, $amount, $commission, $total): BankResultDTO {
            $users = $this->bankUserRepository->lockManyForUpdate([$sender->id, $recipient->id]);
            $lockedSender = $users->get($sender->id);
            $lockedRecipient = $users->get($recipient->id);
            if ($lockedRecipient->bank_account !== $account) {
                return new BankResultDTO(false, 'Счёт не найден.');
            }
            if ($lockedSender->bank_balance < $total) {
                return new BankResultDTO(false, 'Недостаточно монет на счёте (с учётом комиссии).');
            }
            if (! BankBalanceLimits::canCreditAccount((int) $lockedRecipient->bank_balance, $amount)) {
                return new BankResultDTO(false, __('bank.account_limit'));
            }
            if (! $this->bankUserRepository->decrementBankBalance($lockedSender, $total)) {
                return new BankResultDTO(false, 'Недостаточно монет на счёте (с учётом комиссии).');
            }
            $this->bankUserRepository->incrementBankBalance($lockedRecipient, $amount);
            $senderBalance = $this->bankUserRepository->refresh($lockedSender)->bank_balance;
            $recipientBalance = $this->bankUserRepository->refresh($lockedRecipient)->bank_balance;
            $this->bankLogRepository->create($lockedSender->id, BankAction::TRANSFER_OUT, $total, $senderBalance, $lockedRecipient->id);
            $this->bankLogRepository->create($lockedRecipient->id, BankAction::TRANSFER_IN, $amount, $recipientBalance, $lockedSender->id);

            return new BankResultDTO(true, sprintf(
                'Перевод выполнен. Списано: %s (перевод: %s + комиссия: %s) монет.',
                number_format($total), number_format($amount), number_format($commission),
            ));
        });
    }
}
