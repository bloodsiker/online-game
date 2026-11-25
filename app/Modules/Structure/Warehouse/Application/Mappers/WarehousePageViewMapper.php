<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\Mappers;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehouseItemDTO;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehousePageDTO;
use App\Modules\Structure\Warehouse\Infrastructure\Persistence\Models\Warehouse;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;

class WarehousePageViewMapper
{
    public function __construct(private readonly ItemTooltipCollector $itemTooltipCollector) {}

    /**
     * @param  Collection<int, Backpack|Warehouse>  $items
     */
    public function map(User $user, int $warehouseId, int $countInWarehouse, Collection $items): WarehousePageDTO
    {
        $itemModels = $items->map(static fn (Backpack|Warehouse $entry) => $entry->item);
        $this->itemTooltipCollector->collectFrom(new ItemModelTooltipStrategy($itemModels));

        return new WarehousePageDTO(
            warehouseId: $warehouseId,
            money: (int) $user->money,
            diamonds: (int) $user->diamond,
            countInWarehouse: $countInWarehouse,
            warehouseCapacity: (int) $user->warehouse_count,
            items: $items->map(
                static function (Backpack|Warehouse $item): WarehouseItemDTO {
                    $itemInfo = $item->item->itemInfo;
                    $name = (string) $itemInfo->name;

                    if ($itemInfo->type === ShareItemType::TREASURE_MAP) {
                        $territoryName = $item->item->treasureHunt?->targetMap?->name;
                        if (is_string($territoryName) && $territoryName !== '') {
                            $name .= ' — '.$territoryName;
                        }
                    }

                    return new WarehouseItemDTO(
                        id: (int) $item->id,
                        itemId: (int) $item->item_id,
                        count: (int) $item->count,
                        image: (string) $itemInfo->image,
                        name: $name,
                        typeName: (string) $itemInfo->getTypeName(),
                        infoUrl: route('items.info', ['id' => $item->item->id]),
                    );
                }
            )->all(),
            itemTooltipScript: $this->itemTooltipCollector->renderScript(),
        );
    }
}
