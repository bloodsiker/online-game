<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\PickupItemsPageDTO;
use App\Modules\Item\Application\Mappers\PickupItemsPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetPickupItemsPage
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly ItemReadRepository $readRepository,
        private readonly PickupItemsPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $itemId): PickupItemsPageDTO
    {
        $message = $this->itemService->pickUpFromLocation($user, $itemId);

        return $this->mapper->map(
            $this->readRepository->getLocationItems($user),
            $message,
        );
    }
}
