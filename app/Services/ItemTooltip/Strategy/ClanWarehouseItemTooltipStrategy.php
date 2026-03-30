<?php

namespace App\Services\ItemTooltip\Strategy;

use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\ItemTooltipDto;
use App\Services\ItemTooltip\ItemTooltipStatsBuilder;

class ClanWarehouseItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private readonly iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        foreach ($this->items as $warehouseItem) {
            $item     = $warehouseItem->item;
            $itemInfo = $item->itemInfo;

            $collector->add(new ItemTooltipDto(
                id: $item->id,
                title: $itemInfo->name,
                color: $itemInfo->rarity->color(),
                image: $itemInfo->image,
                kind: $itemInfo->getTypeName(),
                price: sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_game.gif'), $itemInfo->price),
                diamond: '',
                lev: ['title' => ' Уровень ', 'value' => '1'],
                skills: [],
                desc: $itemInfo->description,
                store: false,
                nogive: !$itemInfo->is_sell,
                noweight: !$itemInfo->is_weight,
                nosell: !$itemInfo->is_sell,
                stats: ItemTooltipStatsBuilder::build($itemInfo),
            ));
        }
    }
}