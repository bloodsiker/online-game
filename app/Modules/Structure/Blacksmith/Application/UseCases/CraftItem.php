<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\CraftItemDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
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
        $resources = $this->readRepository->getResourceCounts($data->user);

        $recipe = $recipeItem->item->itemInfo->recipe;
        $craftResult = $this->craftService->craft($recipe, $resources);

        if (! $craftResult->resourcesConsumed) {
            return BlacksmithActionResultDTO::fromCraftResult($craftResult);
        }

        return $this->transactionManager->run(function () use ($data, $recipeItem, $recipe, $craftResult) {
            if ($craftResult->craftedShareItemId !== null) {
                $successKraftItem = new Item;
                $successKraftItem->share_item_id = $craftResult->craftedShareItemId;
                $successKraftItem->save();

                $data->user->backpack()->attach($successKraftItem->id, ['equipped' => 0, 'count' => 1]);
            }

            $recipeItem->item->delete();
            $recipeItem->delete();

            foreach ($recipe->items as $itemDelete) {
                $itemBackpack = $this->inventoryRepository->findOwnedSlotByShareItemId($data->user, $itemDelete->id);

                if ($itemBackpack === null) {
                    continue;
                }

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
