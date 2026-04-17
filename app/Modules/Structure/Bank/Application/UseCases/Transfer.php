<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class Transfer
{
    public const COMMISSION = 0.01; // 1%

    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankLogRepository $bankLogRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $sender, string $account, int $amount): BankResultDTO
    {
        $recipient = $this->bankUserRepository->findByBankAccount($account);

        if (! $recipient) {
            return new BankResultDTO(false, 'Счёт не найден.');
        }

        if ($recipient->id === $sender->id) {
            return new BankResultDTO(false, 'Нельзя переводить самому себе.');
        }

        $commission = (int) ceil($amount * self::COMMISSION);
        $total = $amount + $commission;

        if ($sender->bank_balance < $total) {
            return new BankResultDTO(false, 'Недостаточно монет на счёте (с учётом комиссии).');
        }

        $this->transactionManager->run(function () use ($sender, $recipient, $amount, $total) {
            $this->bankUserRepository->decrementBankBalance($sender, $total);
            $this->bankUserRepository->incrementBankBalance($recipient, $amount);

            $senderBalance = $this->bankUserRepository->refresh($sender)->bank_balance;
            $recipientBalance = $this->bankUserRepository->refresh($recipient)->bank_balance;

            $this->bankLogRepository->create($sender->id, BankAction::TRANSFER_OUT, $total, $senderBalance, $recipient->id);
            $this->bankLogRepository->create($recipient->id, BankAction::TRANSFER_IN, $amount, $recipientBalance, $sender->id);
        });

        return new BankResultDTO(true, sprintf(
            'Перевод выполнен. Списано: %s (перевод: %s + комиссия: %s) монет.',
            number_format($total),
            number_format($amount),
            number_format($commission),
        ));
    }
}
