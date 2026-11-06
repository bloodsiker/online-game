<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Contracts;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface BankUserRepository
{
    public function lockForUpdate(int $userId): User;

    /** @return Collection<int, User> Users keyed by ID, locked in ascending ID order. */
    public function lockManyForUpdate(array $userIds): Collection;

    public function save(User $user): void;

    public function refresh(User $user): User;

    public function findByBankAccount(string $account): ?User;

    public function decrementMoney(User $user, int $amount): bool;

    public function incrementMoney(User $user, int $amount): void;

    public function decrementBankBalance(User $user, int $amount): bool;

    public function incrementBankBalance(User $user, int $amount): void;
}
