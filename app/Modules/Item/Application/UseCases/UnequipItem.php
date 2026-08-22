<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\ItemActionResultDTO;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class UnequipItem
{
    public function __construct(
        private readonly ItemService $itemService,
        private readonly ItemReadRepository $readRepository,
        private readonly PlayerStatService $statService,
    ) {}

    public function execute(User $user, int $itemId): ItemActionResultDTO
    {
        $player = $user->player;
        $oldSheet = $this->statService->resolve($player);

        $this->itemService->unequip($user, $itemId);
        $item = $this->readRepository->findItem($itemId);

        $player->refresh();
        $this->statService->invalidate($player);
        $newSheet = $this->statService->resolve($player);
        $this->statService->scaleHp($player, $oldSheet->getHpMax(), $newSheet->getHpMax(), $oldSheet->getMpMax(), $newSheet->getMpMax());

        return new ItemActionResultDTO(
            ok: true,
            hotbarRefresh: $item?->itemInfo?->slot?->value === 'belt',
        );
    }
}
