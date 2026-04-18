<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ChestPageDTO;
use App\Modules\Item\Application\Mappers\ChestPageViewMapper;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class PickUpInChest
{
    public function __construct(
        private readonly ItemReadRepository $readRepository,
        private readonly ItemService $itemService,
        private readonly ChestPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $chestId, int $itemId): ChestPageDTO
    {
        $chest = $this->readRepository->findChestWithItems($chestId);
        abort_if($chest === null, 404);

        $message = $this->itemService->pickUpFromChest($user, $chest, $itemId);

        return $this->mapper->map(
            $this->readRepository->findChestWithItems($chestId),
            $message,
        );
    }
}
