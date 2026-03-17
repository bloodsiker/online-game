<?php

namespace App\Services\ItemTooltip\Strategy;

use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\ItemTooltipDto;

class PremiumShopItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private readonly iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        foreach ($this->items as $shopItem) {
            $itemInfo = $shopItem->item;
            $collector->add(new ItemTooltipDto(
                id: $itemInfo->id,
                title: $itemInfo->name,
                color: '#000000',
                image: $itemInfo->image,
                kind: $itemInfo->getTypeName(),
                price: $itemInfo->price
                    ? sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_game.gif'), $itemInfo->price)
                    : '',
                diamond: $shopItem->diamond
                    ? sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_dmd.gif'), $shopItem->diamond)
                    : '',
                lev: ['title' => ' Уровень ', 'value' => '1'],
                skills: [],
                desc: $itemInfo->description,
                store: true,
                nogive: true,
                noweight: true,
                nosell: true,
            ));
        }
    }
}