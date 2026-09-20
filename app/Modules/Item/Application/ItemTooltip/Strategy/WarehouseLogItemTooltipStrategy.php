<?php

namespace App\Modules\Item\Application\ItemTooltip\Strategy;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipPriceFormatter;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRelationLoader;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;

readonly class WarehouseLogItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        $logs = ItemTooltipRelationLoader::load($this->items, [
            'item.itemInfo.effects',
        ]);

        foreach ($logs as $log) {
            $item = $log->item;
            $itemInfo = $item->itemInfo;
            $collector->add(new ItemTooltipDto(
                id: $item->id,
                title: $itemInfo->name,
                color: $itemInfo->rarity->color(),
                image: $itemInfo->image,
                kind: $itemInfo->getTypeName(),
                price: ItemTooltipPriceFormatter::money((int) $itemInfo->price),
                diamond: '',
                lev: ['title' => ' Уровень ', 'value' => '1'],
                skills: [],
                desc: $itemInfo->description ?? '',
                store: false,
                nogive: true,
                noweight: true,
                nosell: true,
                remainingUses: $item->remainingUses(),
                specialInfo: ItemTooltipStatsBuilder::buildSpecialInfo($itemInfo),
            ));
        }
    }
}
