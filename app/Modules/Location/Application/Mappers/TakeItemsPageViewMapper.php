<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\Mappers;

use App\Modules\Location\Application\DTOs\TakeItemsPageDTO;
use App\Modules\Location\Application\DTOs\TakeLocationItemDTO;
use App\Modules\Share\Domain\Enums\ShareItemType;
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
                    $requiresLockpicking = $isChest
                        && ! $item->item->is_open
                        && ($item->item->itemInfo->lockConfig?->lock_required_skill ?? 0) > 0;

                    return new TakeLocationItemDTO(
                        shareItemId: (int) $item->item->share_item_id,
                        image: (string) $item->item->itemInfo->image,
                        name: (string) $item->item->getName(),
                        count: (int) $item->count,
                        infoUrl: route('items.info.share', ['id' => $item->item->share_item_id]),
                        actionLabel: $isChest
                            ? ($item->item->is_open ? 'Заглянуть' : 'Открыть')
                            : 'Поднять',
                        actionUrl: $isChest
                            ? ($item->item->is_open
                                ? route('items.view_chest', ['id' => $item->item->id])
                                : route('items.open_chest', ['id' => $item->item->id]))
                            : route('items.pick_up', ['id' => $item->item->id]),
                        lockpickingUrl: $requiresLockpicking
                            ? route('items.lockpick.show', ['id' => $item->item->id, 'modal' => 1])
                            : null,
                        rarityColor: $item->item->itemInfo->rarity?->color(),
                    );
                }
            )->all(),
            backUrl: route('location'),
            itemTooltipScript: '',
        );
    }
}
