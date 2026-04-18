<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Structure\Shop\Application\DTOs\ShopBuyPageDTO;
use App\Modules\Structure\Shop\Application\Mappers\ShopBuyPageViewMapper;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetBuyPage
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopBuyPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $shopId): ShopBuyPageDTO
    {
        $shop = $this->readRepository->findStructureOrFail($shopId);

        return $this->mapper->map(
            $user,
            $shop->id,
            $this->readRepository->getShopItems($shop->id),
        );
    }
}
