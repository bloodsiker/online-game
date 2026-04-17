<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Policies;

use App\Models\Share\ShareRecipe;

final class CanCraftRecipe
{
    /**
     * @param  array<int, array{id:int, count:int}>  $resources
     */
    public function check(ShareRecipe $recipe, array $resources): bool
    {
        foreach ($recipe->items as $ingredient) {
            $resourceCount = $ingredient->getCountItemPerRecipe($resources);

            if ($resourceCount < $ingredient->pivot->count) {
                return false;
            }
        }

        return true;
    }
}
