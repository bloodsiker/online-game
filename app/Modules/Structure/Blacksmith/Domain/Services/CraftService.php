<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanCraftRecipe;
use App\Modules\Structure\Blacksmith\Domain\Results\CraftResult;

final class CraftService
{
    public function __construct(
        private readonly CanCraftRecipe $canCraftRecipe,
    ) {}

    /**
     * @param  array<int, array{id:int, count:int}>  $resources
     */
    public function craft(ShareRecipe $recipe, array $resources): CraftResult
    {
        if (! $this->canCraftRecipe->check($recipe, $resources)) {
            return CraftResult::notEnoughResources();
        }

        if (mt_rand(0, 100) > $recipe->percent) {
            return CraftResult::failure();
        }

        $result = $recipe->is_random_result
            ? $this->pickRandomResult($recipe)
            : $recipe->kraftItem;

        if ($result === null) {
            return CraftResult::failure();
        }

        return CraftResult::success($result->id, $result->name);
    }

    /**
     * Взвешенный случайный выбор из пула share_recipe_results - такой же
     * принцип, как у SummonMinionsMechanic::pickWeighted() в Battle-модуле.
     */
    private function pickRandomResult(ShareRecipe $recipe): ?ShareItem
    {
        $pool = $recipe->resultPool;

        if ($pool->isEmpty()) {
            return null;
        }

        $totalWeight = max(1, (int) $pool->sum('pivot.chance'));
        $roll = mt_rand(1, $totalWeight);
        $cumulative = 0;

        foreach ($pool as $item) {
            $cumulative += max(1, (int) $item->pivot->chance);
            if ($roll <= $cumulative) {
                return $item;
            }
        }

        return $pool->last();
    }
}
