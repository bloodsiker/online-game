<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Domain\Contracts;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Warehouse\Infrastructure\Persistence\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

interface WarehouseInventoryRepository
{
    /**
     * @return Collection<int, Backpack>
     */
    public function getBackpackItems(int $userId): Collection;

    /**
     * @param  list<int>  $itemIds
     * @return Collection<int, Backpack>
     */
    public function getBackpackItemsForTransfer(int $userId, array $itemIds): Collection;

    /**
     * @return Collection<int, Warehouse>
     */
    public function getWarehouseItems(int $userId, int $structureId): Collection;

    /**
     * @param  list<int>  $itemIds
     * @return Collection<int, Warehouse>
     */
    public function getWarehouseItemsForTransfer(int $userId, int $structureId, array $itemIds): Collection;

    public function countWarehouseItems(int $userId, int $structureId): int;

    public function findWarehouseStack(int $userId, int $structureId, int $shareItemId): ?Warehouse;

    public function findBackpackStack(int $userId, int $shareItemId): ?Backpack;

    /**
     * @param  list<array{user_id: int, structure_id: int, item_id: int, count: int}>  $items
     */
    public function insertWarehouseItems(array $items): void;

    public function saveWarehouseItem(Warehouse $warehouse): void;

    public function deleteWarehouseItem(Warehouse $warehouse): void;

    public function saveBackpackItem(Backpack $backpack): void;

    /**
     * @param  list<int>  $itemIds
     */
    public function deleteBackpackItems(int $userId, array $itemIds): void;

    public function createBackpackItem(int $userId, int $itemId, int $count): void;
}
