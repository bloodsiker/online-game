<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Support\Collection;

class EloquentShopReadRepository implements ShopReadRepository
{
    public function findStructureOrFail(int $id): Structure
    {
        return Structure::findOrFail($id);
    }

    public function getShopItems(int $structureId, ?int $categoryId = null): Collection
    {
        return ShopItem::with(['item', 'requirements.item'])
            ->where('structure_id', $structureId)
            ->when($categoryId !== null, fn ($query) => $query->where('share_structure_category_id', $categoryId))
            ->orderByDesc('sort_order')
            ->get();
    }

    public function findResourceBackpackItem(int $userId, int $shareItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $userId)
            ->where('items.share_item_id', $shareItemId)
            ->where('backpacks.equipped', 0)
            ->where('items.share_item_id', $shareItemId)
            ->first();
    }

    public function getSellableItems(int $userId): Collection
    {
        return Backpack::select('backpacks.*')
            ->with('item.itemInfo')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->where('backpacks.equipped', 0)
            ->where('share_items.is_sell', 1)
            ->orderBy('items.share_item_id', 'desc')
            ->get();
    }

    public function getSelectedSellableItems(int $userId, array $itemIds): Collection
    {
        return Backpack::select('backpacks.*')
            ->with('item.itemInfo')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->whereIn('item_id', $itemIds)
            ->where('backpacks.equipped', 0)
            ->where('share_items.is_sell', 1)
            ->get();
    }
}
