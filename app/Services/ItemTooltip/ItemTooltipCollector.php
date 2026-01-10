<?php

namespace App\Services\ItemTooltip;

use App\Models\Backpack;
use App\Models\Shop\ShopItem;

class ItemTooltipCollector
{
    /** @var array<int, ItemTooltipDto> */
    private array $items = [];

    public function addFromBackpack(Backpack $backpack): void
    {
        $item = $backpack->item;
        $itemInfo = $item->itemInfo;

        if (isset($this->items[$item->id])) {
            return;
        }

        $this->items[$item->id] = new ItemTooltipDto(
            id: $item->id,
            title: $itemInfo->name,
            color: '#000000',
            image: $itemInfo->image,
            kind: $itemInfo->getTypeName(),
            price: sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_game.gif'), $itemInfo->price),
            diamond: '',
            lev: [
                'title' => ' Уровень ',
                'value' => '1',
            ],
            skills: [],
            desc: $itemInfo->description,
            store: false,
            nogive: 1,
            noweight: 1,
            nosell: 1,
        );
    }

    public function addFromPremiumShop(ShopItem $shopItem): void
    {
        $itemInfo = $shopItem->item;

        if (isset($this->items[$itemInfo->id])) {
            return;
        }

        $this->items[$itemInfo->id] = new ItemTooltipDto(
            id: $itemInfo->id,
            title: $itemInfo->name,
            color: '#000000',
            image: $itemInfo->image,
            kind: $itemInfo->getTypeName(),
            price: $itemInfo->price ? sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_game.gif'), $itemInfo->price) : '',
            diamond: $shopItem->diamond ? sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_dmd.gif'), $shopItem->diamond) : '',
            lev: [
                'title' => ' Уровень ',
                'value' => '1',
            ],
            skills: [],
            desc: $itemInfo->description,
            store: true,
            nogive: 1,
            noweight: 1,
            nosell: 1,
        );
    }

    public function all(): array
    {
        return array_map(
            fn(ItemTooltipDto $dto) => $dto->toArray(),
            $this->items
        );
    }
}
