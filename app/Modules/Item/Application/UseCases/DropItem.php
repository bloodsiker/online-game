<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ItemActionResultDTO;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class DropItem
{
    public function __construct(
        private readonly ItemService $itemService,
    ) {}

    public function execute(User $user, int $itemId, int $confirmation, int $qty): ItemActionResultDTO
    {
        if ($confirmation !== 1) {
            return new ItemActionResultDTO(false);
        }

        $error = $this->itemService->drop($user, $itemId, $qty ?: PHP_INT_MAX);
        if ($error !== null) {
            return new ItemActionResultDTO(false, $error);
        }

        return new ItemActionResultDTO(true);
    }
}
