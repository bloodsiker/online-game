<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\Mappers;

use App\Enums\ShareItemType;
use App\Modules\Location\Application\DTOs\TakeItemsPageDTO;
use App\Modules\Location\Application\DTOs\TakeLocationItemDTO;
use Illuminate\Support\Collection;

class TakeItemsPageViewMapper
{
    public function map(Collection $items): TakeItemsPageDTO
    {
        return new TakeItemsPageDTO(
            count: $items->count(),
            items: $items->map(
                static function ($item): TakeLocationItemDTO {
                    $isChest = $item->item->itemInfo->type === ShareItemType::CHEST;

                    return new TakeLocationItemDTO(
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
            backUrl: route('location'),
        );
    }
}
