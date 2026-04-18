<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Domain\Services\ItemService;

class OpenChest
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly ItemReadRepository $readRepository,
    ) {}

    public function execute(int $itemId): ?int
    {
        $item = $this->readRepository->findItem($itemId);
        if ($item === null) {
            return null;
        }

        $this->itemService->openChest($item);

        return $item->id;
    }
}
