<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\UseCases;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

class GetWarehouseItems
{
    /** @return Collection<int, Warehouse> */
    public function execute(int $userId, int $structureId): Collection
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
}
