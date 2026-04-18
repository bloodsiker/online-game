<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Shop\Domain\Contracts\ShopInventoryRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class EloquentShopInventoryRepository implements ShopInventoryRepository
{
    public function saveUser(User $user): void
    {
        $user->save();
    }

    public function saveBackpackItem(Backpack $backpack): void
    {
        $backpack->save();
    }

    public function deleteBackpackItems(int $userId, array $itemIds): void
    {
        Backpack::where('user_id', $userId)
            ->whereIn('item_id', $itemIds)
            ->delete();
    }

    public function deleteItemsByIds(array $itemIds): void
    {
        Item::whereIn('id', $itemIds)->delete();
    }

    public function createBackpackItem(int $userId, int $shareItemId, int $countUse, int $count): void
    {
        $item = new Item;
        $item->share_item_id = $shareItemId;
        $item->count_use = $countUse;
        $item->save();

        Backpack::create([
            'user_id' => $userId,
            'item_id' => $item->id,
            'count' => $count,
        ]);
    }
}
