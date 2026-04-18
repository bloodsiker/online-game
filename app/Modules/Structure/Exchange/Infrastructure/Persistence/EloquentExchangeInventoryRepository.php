<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Infrastructure\Persistence;

use App\Models\Share\ShareItem;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeInventoryRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class EloquentExchangeInventoryRepository implements ExchangeInventoryRepository
{
    public function getBackpackItems(User $user): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->get();
    }

    public function findShareItem(int $shareItemId): ?ShareItem
    {
        return ShareItem::find($shareItemId);
    }

    public function findBackpackItem(User $user, int $shareItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('items.share_item_id', $shareItemId)
            ->first();
    }

    public function saveBackpackItem(Backpack $backpack): void
    {
        $backpack->save();
    }

    public function deleteBackpackItem(Backpack $backpack): void
    {
        $backpack->delete();
    }

    public function deleteItemById(int $itemId): void
    {
        Item::whereKey($itemId)->delete();
    }

    public function createBackpackItem(User $user, int $shareItemId, int $count): void
    {
        $item = Item::create(['share_item_id' => $shareItemId]);

        Backpack::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'count' => $count,
        ]);
    }
}
