<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

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

        if (mt_rand(0, 100) <= $recipe->percent) {
            return CraftResult::success($recipe->kraftItem->id, $recipe->kraftItem->name);
        }

        return CraftResult::failure();
    }
}
