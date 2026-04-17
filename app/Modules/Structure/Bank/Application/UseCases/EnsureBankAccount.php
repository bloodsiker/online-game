<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\UseCases;

use App\Models\User;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Services\BankAccountGenerator;

class EnsureBankAccount
{
    public function __construct(
        private readonly BankUserRepository $bankUserRepository,
        private readonly BankAccountGenerator $accountGenerator,
    ) {}

    public function execute(User $user): void
    {
        if ($user->bank_account) {
            return;
        }

        $user->bank_account = $this->accountGenerator->generate($user);
        $this->bankUserRepository->save($user);
    }
}
