<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\CraftItemDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Results\CraftResult;
use App\Modules\Structure\Blacksmith\Domain\Services\CraftService;

class CraftItem
{
    public function __construct(
        private readonly BlacksmithInventoryRepository $inventoryRepository,
        private readonly BlacksmithReadRepository $readRepository,
        private readonly TransactionManager $transactionManager,
        private readonly CraftService $craftService,
    ) {}

    public function execute(CraftItemDTO $data): BlacksmithActionResultDTO
    {
        $recipeItem = $this->inventoryRepository->findRecipeSlot($data->user, $data->recipeItemId);
        abort_unless($recipeItem !== null, 404);

        $recipe = $recipeItem->item->itemInfo->recipe;
        if ($recipe->isLearnable()) {
            return new BlacksmithActionResultDTO(
                ok: false,
                message: 'Изучаемый рецепт нужно сначала изучить, а затем использовать в мастерской.',
            );
        }

        return $this->transactionManager->run(function () use ($data, $recipe) {
            $recipeItem = $this->inventoryRepository->findOwnedSlotsForUpdate($data->user, [$data->recipeItemId])->first();
            abort_unless($recipeItem !== null, 404);
            $ingredientSlots = [];
            foreach ($recipe->items->sortBy('id') as $ingredient) {
                $slot = $this->inventoryRepository->findOwnedSlotByShareItemIdForUpdate($data->user, $ingredient->id);
                if ($slot === null || $slot->count < $ingredient->pivot->count) {
                    return BlacksmithActionResultDTO::fromCraftResult(CraftResult::notEnoughResources());
                }
                $ingredientSlots[$ingredient->id] = $slot;
            }
            $resources = $this->readRepository->getResourceCounts($data->user);
            $craftResult = $this->craftService->craft($recipe, $resources);
            if (! $craftResult->resourcesConsumed) {
                return BlacksmithActionResultDTO::fromCraftResult($craftResult);
            }
            if ($craftResult->craftedShareItemId !== null) {
                $successKraftItem = new Item;
                $successKraftItem->share_item_id = $craftResult->craftedShareItemId;
                $successKraftItem->save();

                $data->user->backpack()->attach($successKraftItem->id, ['equipped' => 0, 'count' => 1]);
            }

            if ($recipeItem->count > 1) {
                $recipeItem->decrement('count');
            } else {
                $recipeItem->delete();
                $recipeItem->item->delete();
            }

            foreach ($recipe->items as $itemDelete) {
                $itemBackpack = $ingredientSlots[$itemDelete->id];

                if ($itemBackpack->count > $itemDelete->pivot->count) {
                    $itemBackpack->count -= $itemDelete->pivot->count;
                    $itemBackpack->save();
                } else {
                    $itemBackpack->delete();
                    Item::where('id', $itemBackpack->item_id)->delete();
                }
            }

            return BlacksmithActionResultDTO::fromCraftResult($craftResult);
        });
    }
}
