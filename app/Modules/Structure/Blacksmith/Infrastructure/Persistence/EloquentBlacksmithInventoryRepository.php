<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class EloquentBlacksmithInventoryRepository implements BlacksmithInventoryRepository
{
    public function findRecipeSlot(User $user, int $recipeItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $recipeItemId)
            ->where('share_items.type', ShareItemType::RECIPE->value)
            ->first();
    }

    public function findOwnedSlot(User $user, int $itemId, array $with = []): ?Backpack
    {
        return Backpack::with($with)
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $itemId)
            ->first();
    }

    public function findOwnedSlotByTypes(User $user, int $itemId, array $types, array $with = []): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->with($with)
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $itemId)
            ->whereIn('share_items.type', $types)
            ->first();
    }

    public function findOwnedSlotByShareItemId(User $user, int $shareItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('items.share_item_id', $shareItemId)
            ->first();
    }

    public function findOwnedSlotsForUpdate(User $user, array $itemIds): Collection
    {
        $slots = Backpack::query()
            ->where('user_id', $user->id)
            ->where('equipped', 0)
            ->whereIn('item_id', array_values(array_unique($itemIds)))
            ->orderBy('item_id')
            ->lockForUpdate()
            ->get();

        $items = Item::query()
            ->with(['itemInfo'])
            ->whereKey($slots->pluck('item_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        return $slots->each(function (Backpack $slot) use ($items): void {
            if ($item = $items->get($slot->item_id)) {
                $slot->setRelation('item', $item);
            }
        });
    }

    public function findOwnedSlotByShareItemIdForUpdate(User $user, int $shareItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->with(['item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('items.share_item_id', $shareItemId)
            ->orderBy('backpacks.id')
            ->lockForUpdate()
            ->first();
    }
}
