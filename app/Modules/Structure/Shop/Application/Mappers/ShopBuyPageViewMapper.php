<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Mappers;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\PremiumShopItemTooltipStrategy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\Shop\Application\DTOs\ShopBuyItemDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopBuyPageDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopCartDTO;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class ShopBuyPageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    /**
     * @param  Collection<int, ShopItem>  $shopItems
     * @param  Collection<int, ShareStructureCategory>  $categories
     */
    public function map(
        User $user,
        int $shopId,
        Collection $shopItems,
        Collection $categories,
        ?int $activeCategoryId,
        ShopCartDTO $cart,
    ): ShopBuyPageDTO {
        // Тултипы нужны и для товаров вне активной категории, лежащих в корзине:
        // собираем их вместе с сеткой (дубликаты коллектор убирает по id).
        $tooltipItems = $shopItems->merge(
            $cart->getItems()->map(fn ($cartItem) => $cartItem->shopItem)->filter(),
        );

        $itemTooltipScript = $this->collector
            ->collectFrom(new PremiumShopItemTooltipStrategy($tooltipItems))
            ->renderScript();

        return new ShopBuyPageDTO(
            shopId: $shopId,
            money: (int) $user->money,
            diamonds: (int) $user->diamond,
            items: $shopItems->map(
                static fn (ShopItem $item): ShopBuyItemDTO => new ShopBuyItemDTO(
                    shopItemId: (int) $item->id,
                    itemId: (int) $item->item->id,
                    name: (string) $item->item->name,
                    color: $item->item->rarity?->color() ?? '#666666',
                    image: (string) $item->item->image,
                    typeName: (string) $item->item->getTypeName(),
                    price: (int) $item->price,
                    diamond: (int) $item->diamond,
                    infoUrl: route('items.info.share', ['id' => $item->item->id]),
                )
            )->all(),
            categories: $categories->map(
                static fn (ShareStructureCategory $category): array => [
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                ]
            )->all(),
            activeCategoryId: $activeCategoryId,
            cart: $cart,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
