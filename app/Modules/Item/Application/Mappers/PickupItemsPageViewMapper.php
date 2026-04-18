<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Mappers;

use App\Enums\ShareItemType;
use App\Modules\Item\Application\DTOs\ItemLocationEntryDTO;
use App\Modules\Item\Application\DTOs\PickupItemsPageDTO;
use Illuminate\Support\Collection;

class PickupItemsPageViewMapper
{
    public function map(Collection $items, string $message): PickupItemsPageDTO
    {
        return new PickupItemsPageDTO(
            count: $items->count(),
            items: $items->map(
                static function ($item): ItemLocationEntryDTO {
                    $isChest = $item->item->itemInfo->type === ShareItemType::CHEST;

                    return new ItemLocationEntryDTO(
                        image: (string) $item->item->itemInfo->image,
                        name: (string) $item->item->getName(),
                        count: (int) $item->count,
                        actionLabel: $isChest
                            ? ($item->item->is_open ? 'Заглянуть' : 'Открыть')
                            : 'Поднять',
                        actionUrl: $isChest
                            ? ($item->item->is_open
                                ? route('items.view_chest', ['id' => $item->item->id])
                                : route('items.open_chest', ['id' => $item->item->id]))
                            : route('items.pick_up', ['id' => $item->item->id]),
                    );
                }
            )->all(),
            message: $message,
            locationUrl: route('location'),
            backpackUrl: route('backpack'),
        );
    }
}
