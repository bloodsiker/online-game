<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ChestPageDTO;
use App\Modules\Item\Application\Mappers\ChestPageViewMapper;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class PickUpInChest
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly ChestPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $chestId, int $itemId): ChestPageDTO
    {
        $message = $this->itemService->pickUpFromChest($user, $chestId, $itemId);
        $context = $this->itemService->accessibleChest($user, $chestId);

        return $this->mapper->map(
            $context === null ? null : $context[0]->load('itemsInChest'),
            $message,
        );
    }
}
