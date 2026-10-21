<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Structure\Blacksmith\Application\DTOs\KraftPageDTO;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Support\Collection;

class KraftPageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    /**
     * @param  Collection<int, mixed>  $recipes
     * @param  array<int, array{id:int, count:int}>  $resources
     * @param  array<int, array<string, mixed>>  $assemblies
     */
    public function map(
        Structure $blacksmith,
        Collection $recipes,
        array $resources,
        array $assemblies = [],
        Collection $assemblyItems = new Collection,
    ): KraftPageDTO {
        $ingredientItems = $recipes->flatMap(fn ($recipe) => $recipe->item->itemInfo->recipe?->items ?? collect());
        $resultItems = $recipes->flatMap(function ($recipe) {
            $recipeModel = $recipe->item->itemInfo->recipe;

            if ($recipeModel === null) {
                return collect();
            }

            return $recipeModel->is_random_result
                ? $recipeModel->resultPool
                : collect([$recipeModel->kraftItem])->filter();
        });
        $shareItems = $ingredientItems
            ->concat($resultItems)
            ->unique('id')
            ->values();

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($recipes))
            ->collectFrom(new ShareItemTooltipStrategy($shareItems->concat($assemblyItems)->unique('id')->values()))
            ->renderScript();

        $recipeViews = $recipes->map(function ($recipe) use ($resources) {
            $recipeModel = $recipe->item->itemInfo->recipe;

            $ingredients = collect($recipeModel->items)->map(function ($item) use ($resources) {
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

            $isRandomResult = $recipeModel->is_random_result;
            $totalChance = $isRandomResult ? max(1, $recipeModel->resultPool->sum('pivot.chance')) : 0;

            return [
                'recipeItemId' => $recipe->item->id,
                'recipeName' => $recipe->item->itemInfo->name,
                'recipeImage' => $recipe->item->itemInfo->image,
                'recipeRarityColor' => $recipe->item->itemInfo->rarity->color(),
                'chancePercent' => $recipeModel->percent,
                'ingredients' => $ingredients,
                'isRandomResult' => $isRandomResult,
                'resultId' => $isRandomResult ? null : $recipeModel->kraftItem->id,
                'resultName' => $isRandomResult ? null : $recipeModel->kraftItem->name,
                'resultImage' => $isRandomResult ? null : $recipeModel->kraftItem->image,
                'resultRarityColor' => $isRandomResult ? null : $recipeModel->kraftItem->rarity->color(),
                'resultPool' => $isRandomResult ? $recipeModel->resultPool->map(fn ($poolItem) => [
                    'id' => $poolItem->id,
                    'name' => $poolItem->name,
                    'image' => $poolItem->image,
                    'rarityColor' => $poolItem->rarity->color(),
                    'chancePercent' => round($poolItem->pivot->chance / $totalChance * 100, 1),
                ])->values()->all() : [],
                'canCraft' => collect($ingredients)->every(fn ($ingredient) => $ingredient['active']),
            ];
        })->values()->all();

        return new KraftPageDTO(
            blacksmith: $blacksmith,
            recipes: $recipeViews,
            resources: $resources,
            itemTooltipScript: $itemTooltipScript,
            assemblies: $assemblies,
        );
    }
}
