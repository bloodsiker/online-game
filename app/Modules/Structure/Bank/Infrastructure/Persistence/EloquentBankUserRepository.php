<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence;

use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class EloquentBankUserRepository implements BankUserRepository
{
    public function save(User $user): void
    {
        $user->save();
    }

    public function refresh(User $user): User
    {
        return $user->fresh();
    }

    public function findByBankAccount(string $account): ?User
    {
        return User::where('bank_account', $account)->first();
    }

    public function decrementMoney(User $user, int $amount): void
    {
        $user->decrement('money', $amount);
    }

    public function incrementMoney(User $user, int $amount): void
    {
        $user->increment('money', $amount);
    }

    public function decrementBankBalance(User $user, int $amount): void
    {
        $user->decrement('bank_balance', $amount);
    }

    public function incrementBankBalance(User $user, int $amount): void
    {
        $user->increment('bank_balance', $amount);
    }
}
