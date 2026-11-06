<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Domain\Contracts;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\User\Infrastructure\Persistence\Models\User;

interface ShopInventoryRepository
{
    public function lockUser(int $userId): User;

    public function creditMoney(User $user, int $amount): void;

    public function consumeBackpackItem(Backpack $backpack, int $quantity): bool;

    public function createBackpackItem(int $userId, int $shareItemId, int $countUse, int $count): void;
}
