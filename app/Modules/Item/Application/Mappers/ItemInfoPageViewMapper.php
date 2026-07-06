<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Mappers;

use App\Modules\Item\Application\DTOs\ItemInfoPageDTO;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Services\ItemTooltip\ItemTooltipStatsBuilder;

class ItemInfoPageViewMapper
{
    public function map(Item $item): ItemInfoPageDTO
    {
        $shareItem = $item->itemInfo;
        $shareItem->loadMissing('stats', 'effects', 'requirements.skill');

        $name = (string) $item->getName();
        if ($item->upgrade_lvl > 0) {
            $name .= ' +'.$item->upgrade_lvl;
        }

        $requirements = [];
        foreach ($shareItem->requirements as $requirement) {
            $requirements[] = [
                'label' => $requirement->label(),
                'value' => (int) $requirement->min_value,
            ];
        }

        return new ItemInfoPageDTO(
            itemId: (int) $item->id,
            shareItemId: (int) $shareItem->id,
            name: $name,
            color: $shareItem->rarity?->color() ?? '#333333',
            image: $shareItem->image ? asset($shareItem->image) : null,
            typeName: (string) $shareItem->getTypeName(),
            price: (int) $shareItem->price,
            description: $shareItem->description,
            noGive: ! $shareItem->is_sell,
            noWeight: ! $shareItem->is_weight,
            noSell: ! $shareItem->is_sell,
            stats: ItemTooltipStatsBuilder::build($shareItem),
            requirements: $requirements,
            handOverUrl: route('items.hand_over', ['id' => $item->id]),
            dropUrl: route('items.drop', ['id' => $item->id]),
            sameItemsUrl: route('backpack', ['sid' => $shareItem->id]),
            backpackUrl: route('backpack'),
        );
    }
}
