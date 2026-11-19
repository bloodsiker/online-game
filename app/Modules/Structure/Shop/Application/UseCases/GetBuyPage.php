<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Application\DTOs\ShopBuyPageDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopFilterDTO;
use App\Modules\Structure\Shop\Application\Mappers\ShopBuyPageViewMapper;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\Structure\Shop\Application\Services\ShopItemFilter;
use App\Modules\Structure\Shop\Application\Services\StructureShopAccessService;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetBuyPage
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopBuyPageViewMapper $mapper,
        private readonly ShopCartService $shopCartService,
        private readonly StructureShopAccessService $access = new StructureShopAccessService,
        private readonly ShopItemFilter $itemFilter = new ShopItemFilter,
    ) {}

    public function execute(
        User $user,
        int $shopId,
        ?int $categoryId = null,
        string $expectedType = Structure::TYPE_SHOP,
        ShopFilterDTO $filter = new ShopFilterDTO,
    ): ShopBuyPageDTO {
        $shop = $this->readRepository->findStructureOrFail($shopId);
        abort_unless($shop->type === $expectedType, 404);
        abort_unless($this->access->canVisit($user, $shop), 403, __('commerce.shop_requires_npc'));
        $categories = $shop->categories;

        $activeCategoryId = $categories->isNotEmpty()
            ? ($categoryId ?? $categories->first()->id)
            : null;

        $items = $this->readRepository->getShopItems($shop->id, $activeCategoryId);
        $cart = $this->shopCartService->getCart($user, $shop->id);
        $requirementItemIds = $items
            ->flatMap(static fn ($item) => $item->requirements->pluck('share_item_id'))
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
        $backpackShareItemCounts = $this->readRepository->getBackpackShareItemCounts(
            (int) $user->id,
            $requirementItemIds,
        );

        $page = $this->mapper->map(
            $user,
            $shop,
            $items,
            $categories,
            $activeCategoryId,
            $cart,
            $backpackShareItemCounts,
        );

        $playerLevel = (int) ($user->player?->lvl ?? 0);

        return new ShopBuyPageDTO(
            shopId: $page->shopId,
            shopType: $page->shopType,
            shopName: $page->shopName,
            shopDescription: $page->shopDescription,
            money: $page->money,
            diamonds: $page->diamonds,
            items: $this->itemFilter->apply(
                items: $page->items,
                filter: $filter,
                playerLevel: $playerLevel,
                reputationPoints: $this->access->reputationPoints($user, $shop),
                money: $page->money,
                diamonds: $page->diamonds,
            ),
            categories: $page->categories,
            activeCategoryId: $page->activeCategoryId,
            cart: $page->cart,
            itemTooltipScript: $page->itemTooltipScript,
            filter: $filter,
            totalItemsCount: count($page->items),
            playerLevel: $playerLevel,
        );
    }
}
