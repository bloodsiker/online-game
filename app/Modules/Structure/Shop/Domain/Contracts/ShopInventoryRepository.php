<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Domain\Contracts;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\User\Infrastructure\Persistence\Models\User;

interface ShopInventoryRepository
{
    public function saveUser(User $user): void;

    public function saveBackpackItem(Backpack $backpack): void;

    /**
     * @param  list<int>  $itemIds
     */
    public function deleteBackpackItems(int $userId, array $itemIds): void;

    /**
     * @param  list<int>  $itemIds
     */
    public function deleteItemsByIds(array $itemIds): void;

    public function createBackpackItem(int $userId, int $shareItemId, int $countUse, int $count): void;
}
