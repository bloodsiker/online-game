<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Structure\Shop\Application\DTOs\ShopSellPageDTO;
use App\Modules\Structure\Shop\Application\Mappers\ShopSellPageViewMapper;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetSellPage
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopSellPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $shopId): ShopSellPageDTO
    {
        $shop = $this->readRepository->findStructureOrFail($shopId);

        return $this->mapper->map(
            $user,
            $shop->id,
            $this->readRepository->getSellableItems($user->id),
        );
    }
}
