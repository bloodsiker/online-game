<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Modules\Structure\Bank\Application\DTOs\BankLookupResultDTO;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class LookupRecipient
{
    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
    ) {}

    public function execute(User $sender, string $account): BankLookupResultDTO
    {
        $recipient = $this->bankUserRepository->findByBankAccount($account);

        if ($recipient === null) {
            return new BankLookupResultDTO(false, error: 'Счёт не найден', status: 404);
        }

        if ($recipient->id === $sender->id) {
            return new BankLookupResultDTO(false, error: 'Нельзя переводить самому себе', status: 422);
        }

        return new BankLookupResultDTO(true, name: $recipient->name);
    }
}
