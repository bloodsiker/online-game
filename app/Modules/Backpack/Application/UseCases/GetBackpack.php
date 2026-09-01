<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetBackpack
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ItemTooltipCollector $collector,
        private readonly LocationReadRepository $locationReadRepository,
        private readonly ItemReadRepository $itemReadRepository,
    ) {}

    public function execute(User $user, array $filters = []): array
    {
        $data = $this->backpackService->getBackpackData($user, $filters);
        $playerEquip = $user->player->playerEquip;
        $teleportUseShareItemIds = $this->locationReadRepository
            ->getTeleportUseShareItemIds((int) $user->location_id);
        $teleportUseKeyItemIds = $data->getBackpack()
            ->filter(
                static fn ($backpack): bool => in_array(
                    (int) $backpack->item->share_item_id,
                    $teleportUseShareItemIds,
                    true,
                )
            )
            ->pluck('item_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
        $droppableItemIds = $data->getBackpack()
            ->filter(static fn ($backpack): bool => $backpack->item->itemInfo->is_droppable)
            ->pluck('item_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
        $debuffTargetItemIds = $data->getBackpack()
            ->filter(fn ($backpack): bool => $backpack->item->itemInfo->relationLoaded('debuffs')
                && $backpack->item->itemInfo->debuffs->isNotEmpty())
            ->pluck('item_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
        $learnableRecipeItemIds = $data->getBackpack()
            ->filter(static function ($backpack): bool {
                $shareItem = $backpack->item->itemInfo;

                return $shareItem->relationLoaded('recipe') && $shareItem->recipe?->isLearnable();
            })
            ->pluck('item_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
        $debuffTargets = $this->itemReadRepository->getOnlineUsersOnLocation($user)
            ->filter(fn (User $target): bool => $target->player !== null)
            ->map(fn (User $target): array => ['id' => $target->player->id, 'name' => $target->name])
            ->values()
            ->all();

        $this->collector->collectFrom(new BackpackItemTooltipStrategy($data->getBackpack()));
        $itemTooltipScript = $this->collector->renderScript();

        return compact('data', 'user', 'playerEquip', 'itemTooltipScript', 'teleportUseKeyItemIds', 'droppableItemIds', 'debuffTargetItemIds', 'debuffTargets', 'learnableRecipeItemIds');
    }
}
