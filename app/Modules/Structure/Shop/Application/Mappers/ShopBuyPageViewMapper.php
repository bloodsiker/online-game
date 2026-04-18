<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Mappers;

use App\Modules\Structure\Shop\Application\DTOs\ShopBuyItemDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopBuyPageDTO;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class ShopBuyPageViewMapper
{
    /**
     * @param  Collection<int, ShopItem>  $shopItems
     */
    public function map(User $user, int $shopId, Collection $shopItems): ShopBuyPageDTO
    {
        return new ShopBuyPageDTO(
            shopId: $shopId,
            money: (int) $user->money,
            diamonds: (int) $user->diamond,
            items: $shopItems->map(
                static fn (ShopItem $item): ShopBuyItemDTO => new ShopBuyItemDTO(
                    shareItemId: (int) $item->item->id,
                    name: (string) $item->item->name,
                    image: (string) $item->item->image,
                    typeName: (string) $item->item->getTypeName(),
                    price: (int) $item->price,
                    infoUrl: route('items.info', ['id' => $item->item->id]),
                    buyUrl: route('shop.buy_item', ['id' => $shopId, 'itemId' => $item->item->id]),
                )
            )->all(),
        );
    }
}
