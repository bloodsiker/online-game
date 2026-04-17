<?php

namespace App\Services\ItemTooltip\Strategy;

use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\ItemTooltipDto;

readonly class WarehouseLogItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        foreach ($this->items as $log) {
            $item = $log->item;
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
                nogive: true,
                noweight: true,
                nosell: true,
            ));
        }
    }
}
