<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Mappers;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\PremiumShopItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
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
     * @param  Collection<int, int>  $backpackShareItemCounts
     */
    public function map(
        User $user,
        Structure $shop,
        Collection $shopItems,
        Collection $categories,
        ?int $activeCategoryId,
        ShopCartDTO $cart,
        Collection $backpackShareItemCounts,
    ): ShopBuyPageDTO {
        // Тултипы нужны и для товаров вне активной категории, лежащих в корзине:
        // собираем их вместе с сеткой (дубликаты коллектор убирает по id).
        $tooltipItems = $shopItems->merge(
            $cart->getItems()->map(fn ($cartItem) => $cartItem->shopItem)->filter(),
        );

        $requirementItems = $tooltipItems
            ->flatMap(fn (ShopItem $shopItem) => $shopItem->requirements->pluck('item'))
            ->filter()
            ->unique('id')
            ->values();

        $this->collector->collectFrom(new PremiumShopItemTooltipStrategy($tooltipItems));
        if ($requirementItems->isNotEmpty()) {
            $this->collector->collectFrom(new ShareItemTooltipStrategy($requirementItems));
        }

        return new ShopBuyPageDTO(
            shopId: (int) $shop->id,
            shopType: (string) $shop->type,
            shopName: (string) $shop->name,
            shopDescription: $shop->description !== null && trim((string) $shop->description) !== '' ? (string) $shop->description : null,
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
                    requiredLevel: $item->item->requirements
                        ->first(static fn ($requirement): bool => $requirement->type === ShareItemRequirementType::LEVEL)
                        ?->min_value,
                    price: (int) $item->price,
                    diamond: (int) $item->diamond,
                    requirements: $item->requirements
                        ->filter(fn ($requirement): bool => $requirement->item !== null)
                        ->map(static fn ($requirement): array => [
                            'id' => (int) $requirement->item->id,
                            'name' => (string) $requirement->item->name,
                            'image' => (string) $requirement->item->image,
                            'color' => $requirement->item->rarity?->color() ?? '#666666',
                            'quantity' => (int) $requirement->quantity,
                            'availableQuantity' => (int) $backpackShareItemCounts->get($requirement->item->id, 0),
                            'infoUrl' => route('items.info.share', ['id' => $requirement->item->id]),
                        ])
                        ->values()
                        ->all(),
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
            itemTooltipScript: $this->collector->renderScript(),
        );
    }
}
