<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Models\Structure;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Structure\Blacksmith\Application\DTOs\KraftPageDTO;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use Illuminate\Support\Collection;

class KraftPageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    /**
     * @param  Collection<int, mixed>  $recipes
     * @param  array<int, array{id:int, count:int}>  $resources
     */
    public function map(Structure $blacksmith, Collection $recipes, array $resources): KraftPageDTO
    {
        $ingredientItems = $recipes->flatMap(fn ($recipe) => $recipe->item->itemInfo->recipe?->items ?? collect());

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($recipes))
            ->collectFrom(new ShareItemTooltipStrategy($ingredientItems))
            ->renderScript();

        $recipeViews = $recipes->map(function ($recipe) use ($resources) {
            $ingredients = collect($recipe->item->itemInfo->recipe->items)->map(function ($item) use ($resources) {
                $itemsHas = $item->getCountItemPerRecipe($resources);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $item->image,
                    'requiredCount' => $item->pivot->count,
                    'availableCount' => $itemsHas,
                    'active' => $itemsHas >= $item->pivot->count,
                ];
            })->values()->all();

            return [
                'recipeItemId' => $recipe->item->id,
                'recipeName' => $recipe->item->itemInfo->name,
                'recipeImage' => $recipe->item->itemInfo->image,
                'chancePercent' => $recipe->item->itemInfo->recipe->percent,
                'ingredients' => $ingredients,
                'resultImage' => $recipe->item->itemInfo->recipe->kraftItem->image,
                'canCraft' => collect($ingredients)->every(fn ($ingredient) => $ingredient['active']),
            ];
        })->values()->all();

        return new KraftPageDTO(
            blacksmith: $blacksmith,
            recipes: $recipeViews,
            resources: $resources,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
