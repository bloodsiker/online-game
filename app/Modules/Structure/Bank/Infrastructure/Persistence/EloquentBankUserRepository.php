<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence;

use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class EloquentBankUserRepository implements BankUserRepository
{
    public function lockForUpdate(int $userId): User
    {
        return User::without('player')->whereKey($userId)->lockForUpdate()->firstOrFail();
    }

    public function lockManyForUpdate(array $userIds): Collection
    {
        return collect($userIds)->map(fn ($id): int => (int) $id)->unique()->sort()->values()
            ->mapWithKeys(fn (int $id): array => [$id => $this->lockForUpdate($id)]);
    }

    public function save(User $user): void
    {
        $user->save();
    }

    public function refresh(User $user): User
    {
        return User::without('player')->findOrFail($user->id);
    }

    public function findByBankAccount(string $account): ?User
    {
        return User::without('player')->where('bank_account', $account)->first();
    }

    public function decrementMoney(User $user, int $amount): bool
    {
        return $amount > 0 && User::query()->whereKey($user->id)->where('money', '>=', $amount)->decrement('money', $amount) === 1;
    }

    public function incrementMoney(User $user, int $amount): void
    {
        $user->increment('money', $amount);
    }

    public function decrementBankBalance(User $user, int $amount): bool
    {
        return $amount > 0 && User::query()->whereKey($user->id)->where('bank_balance', '>=', $amount)->decrement('bank_balance', $amount) === 1;
    }

    public function incrementBankBalance(User $user, int $amount): void
    {
        $user->increment('bank_balance', $amount);
    }
}
