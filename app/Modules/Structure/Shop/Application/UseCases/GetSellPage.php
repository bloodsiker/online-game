<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Structure\Shop\Application\DTOs\ShopSellPageDTO;
use App\Modules\Structure\Shop\Application\Mappers\ShopSellPageViewMapper;
use App\Modules\Structure\Shop\Application\Services\ShopSellCartService;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetSellPage
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopSellPageViewMapper $mapper,
        private readonly ShopSellCartService $sellCartService,
        private readonly BackpackService $backpackService,
    ) {}

    public function execute(User $user, int $shopId): ShopSellPageDTO
    {
        $shop = $this->readRepository->findStructureOrFail($shopId);

        $items = $this->readRepository->getSellableItems($user->id);

        return $this->mapper->map(
            $user,
            $shop->id,
            $items,
            $this->sellCartService->getCart($user, (int) $shop->id, $items),
            $this->backpackService->getCountableItemsCount($user),
            $user->getBagCount(),
        );
    }
}
