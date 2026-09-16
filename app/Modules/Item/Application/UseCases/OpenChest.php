<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class OpenChest
{
    public function __construct(
        private readonly ItemService $itemService,
    ) {}

    public function execute(User $user, int $itemId): ?int
    {
        return $this->itemService->openChest($user, $itemId);
    }
}
