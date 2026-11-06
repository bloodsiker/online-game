<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Shop\Domain\Contracts\ShopInventoryRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class EloquentShopInventoryRepository implements ShopInventoryRepository
{
    public function lockUser(int $userId): User
    {
        return User::without('player')->whereKey($userId)->lockForUpdate()->firstOrFail();
    }

    public function creditMoney(User $user, int $amount): void
    {
        if ($amount > 0) {
            $user->increment('money', $amount);
        }
    }

    public function consumeBackpackItem(Backpack $backpack, int $quantity): bool
    {
        if ($quantity <= 0 || Backpack::query()->whereKey($backpack->id)
            ->where('user_id', $backpack->user_id)->where('equipped', 0)
            ->where('count', '>=', $quantity)->decrement('count', $quantity) !== 1) {
            return false;
        }
        Backpack::query()->whereKey($backpack->id)->where('count', 0)->delete();
        if (! Backpack::query()->where('item_id', $backpack->item_id)->exists()) {
            Item::query()->whereKey($backpack->item_id)->delete();
        }

        return true;
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
