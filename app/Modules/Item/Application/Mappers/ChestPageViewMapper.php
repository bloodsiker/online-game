<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Mappers;

use App\Modules\Item\Application\DTOs\ChestEntryDTO;
use App\Modules\Item\Application\DTOs\ChestPageDTO;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;

class ChestPageViewMapper
{
    public function map(?Item $chest, string $message = ''): ChestPageDTO
    {
        $items = $chest?->itemsInChest ?? collect();
        $chestId = $chest?->id;

        return new ChestPageDTO(
            count: $items->count(),
            items: $items->map(
                static fn (Item $item): ChestEntryDTO => new ChestEntryDTO(
                    image: (string) $item->itemInfo->image,
                    name: (string) $item->getName(),
                    count: (int) $item->pivot->count,
                    pickupUrl: route('items.pickup_chest', ['chest' => $chestId, 'id' => $item->id]),
                )
            )->all(),
            message: $message,
            backpackUrl: route('backpack'),
            locationUrl: route('location'),
        );
    }
}
