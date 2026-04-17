<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\Mappers;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehouseItemDTO;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehousePageDTO;
use App\Modules\Structure\Warehouse\Infrastructure\Persistence\Models\Warehouse;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;

class WarehousePageViewMapper
{
    /**
     * @param  Collection<int, Backpack|Warehouse>  $items
     */
    public function map(User $user, int $warehouseId, int $countInWarehouse, Collection $items): WarehousePageDTO
    {
        return new WarehousePageDTO(
            warehouseId: $warehouseId,
            money: (int) $user->money,
            diamonds: (int) $user->diamond,
            countInWarehouse: $countInWarehouse,
            warehouseCapacity: (int) $user->warehouse_count,
            items: $items->map(
                static fn (Backpack|Warehouse $item): WarehouseItemDTO => new WarehouseItemDTO(
                    id: (int) $item->id,
                    itemId: (int) $item->item_id,
                    count: (int) $item->count,
                    image: (string) $item->item->itemInfo->image,
                    name: (string) $item->item->itemInfo->name,
                    typeName: (string) $item->item->itemInfo->getTypeName(),
                    infoUrl: route('items.info', ['id' => $item->item->id]),
                )
            )->all(),
        );
    }
}
