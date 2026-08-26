<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

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
}
