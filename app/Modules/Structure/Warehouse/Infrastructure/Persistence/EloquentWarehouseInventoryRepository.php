<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Infrastructure\Persistence;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Warehouse\Domain\Contracts\WarehouseInventoryRepository;
use App\Modules\Structure\Warehouse\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

class EloquentWarehouseInventoryRepository implements WarehouseInventoryRepository
{
    public function getBackpackItems(int $userId): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->where('equipped', 0)
            ->orderBy('share_items.type', 'desc')
            ->get();
    }

    public function getBackpackItemsForTransfer(int $userId, array $itemIds): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->whereIn('backpacks.item_id', $itemIds)
            ->where('equipped', 0)
            ->get();
    }

    public function getWarehouseItems(int $userId, int $structureId): Collection
    {
        return Warehouse::select('warehouses.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'warehouses.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('user_id', $userId)
            ->where('structure_id', $structureId)
            ->orderBy('share_items.type', 'desc')
            ->get();
    }

    public function getWarehouseItemsForTransfer(int $userId, int $structureId, array $itemIds): Collection
    {
        return Warehouse::with(['item', 'item.itemInfo'])
            ->where('structure_id', $structureId)
            ->where('user_id', $userId)
            ->whereIn('item_id', $itemIds)
            ->get();
    }

    public function countWarehouseItems(int $userId, int $structureId): int
    {
        return Warehouse::where('user_id', $userId)
            ->where('structure_id', $structureId)
            ->count();
    }

    public function findWarehouseStack(int $userId, int $structureId, int $shareItemId): ?Warehouse
    {
        return Warehouse::select('warehouses.*')
            ->join('items', 'warehouses.item_id', '=', 'items.id')
            ->where('items.share_item_id', $shareItemId)
            ->where('warehouses.user_id', $userId)
            ->where('warehouses.structure_id', $structureId)
            ->first();
    }

    public function findBackpackStack(int $userId, int $shareItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('items.share_item_id', $shareItemId)
            ->where('backpacks.user_id', $userId)
            ->first();
    }

    public function insertWarehouseItems(array $items): void
    {
        Warehouse::insert($items);
    }

    public function saveWarehouseItem(Warehouse $warehouse): void
    {
        $warehouse->save();
    }

    public function deleteWarehouseItem(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    public function saveBackpackItem(Backpack $backpack): void
    {
        $backpack->save();
    }

    public function deleteBackpackItems(int $userId, array $itemIds): void
    {
        Backpack::whereIn('item_id', $itemIds)
            ->where('user_id', $userId)
            ->delete();
    }

    public function createBackpackItem(int $userId, int $itemId, int $count): void
    {
        Backpack::create([
            'user_id' => $userId,
            'item_id' => $itemId,
            'count' => $count,
        ]);
    }
}
