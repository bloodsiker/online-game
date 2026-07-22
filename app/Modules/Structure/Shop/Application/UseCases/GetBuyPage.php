<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Structure\Shop\Application\DTOs\ShopBuyPageDTO;
use App\Modules\Structure\Shop\Application\Mappers\ShopBuyPageViewMapper;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetBuyPage
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopBuyPageViewMapper $mapper,
        private readonly ShopCartService $shopCartService,
    ) {}

    public function execute(User $user, int $shopId, ?int $categoryId = null): ShopBuyPageDTO
    {
        $shop = $this->readRepository->findStructureOrFail($shopId);
        $categories = $shop->categories;

        $activeCategoryId = $categories->isNotEmpty()
            ? ($categoryId ?? $categories->first()->id)
            : null;

        $items = $this->readRepository->getShopItems($shop->id, $activeCategoryId);
        $cart = $this->shopCartService->getCart($user, $shop->id);

        return $this->mapper->map($user, $shop->id, $items, $categories, $activeCategoryId, $cart);
    }
}
