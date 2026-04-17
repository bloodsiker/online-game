<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Contracts;

use App\Modules\User\Infrastructure\Persistence\Models\User;

interface BankUserRepository
{
    public function save(User $user): void;

    public function refresh(User $user): User;

    public function findByBankAccount(string $account): ?User;

    public function decrementMoney(User $user, int $amount): void;

    public function incrementMoney(User $user, int $amount): void;

    public function decrementBankBalance(User $user, int $amount): void;

    public function incrementBankBalance(User $user, int $amount): void;
}
