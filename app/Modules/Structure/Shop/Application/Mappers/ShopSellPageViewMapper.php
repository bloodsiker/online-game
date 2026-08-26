<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Mappers;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Structure\Shop\Application\DTOs\ShopSellItemDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopSellPageDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class ShopSellPageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    /**
     * @param  Collection<int, Backpack>  $items
     */
    public function map(User $user, int $shopId, Collection $items): ShopSellPageDTO
    {
        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->renderScript();

        return new ShopSellPageDTO(
            shopId: $shopId,
            money: (int) $user->money,
            diamonds: (int) $user->diamond,
            items: $items->map(
                static fn (Backpack $item): ShopSellItemDTO => new ShopSellItemDTO(
                    backpackId: (int) $item->id,
                    itemId: (int) $item->item_id,
                    count: (int) $item->count,
                    image: (string) $item->item->itemInfo->image,
                    name: (string) $item->item->itemInfo->name,
                    color: $item->item->itemInfo->rarity?->color() ?? '#666666',
                    typeName: (string) $item->item->itemInfo->getTypeName(),
                    sellPrice: (int) round($item->item->itemInfo->price / 2),
                    infoUrl: route('items.info', ['id' => $item->item->id]),
                )
            )->all(),
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
