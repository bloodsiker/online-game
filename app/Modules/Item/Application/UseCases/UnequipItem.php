<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ItemActionResultDTO;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class UnequipItem
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly ItemReadRepository $readRepository,
    ) {}

    public function execute(User $user, int $itemId): ItemActionResultDTO
    {
        $this->itemService->unequip($user, $itemId);
        $item = $this->readRepository->findItem($itemId);

        return new ItemActionResultDTO(
            ok: true,
            hotbarRefresh: $item?->itemInfo?->slot?->value === 'belt',
        );
    }
}
