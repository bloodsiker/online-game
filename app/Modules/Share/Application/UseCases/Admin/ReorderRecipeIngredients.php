<?php

declare(strict_types=1);

namespace App\Modules\Share\Application\UseCases\Admin;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderRecipeIngredients
{
    /** @param Collection<int, int> $ids */
    public function execute(ShareRecipe $recipe, Collection $ids): void
    {
        DB::transaction(function () use ($recipe, $ids): void {
            ShareRecipe::whereKey($recipe->id)->lockForUpdate()->firstOrFail();
            $rows = DB::table('share_recipe_has_items')->where('share_recipe_id', $recipe->id)->orderBy('id')->lockForUpdate()->get();
            if ($ids->sort()->values()->all() !== $rows->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values()->all()) {
                throw ValidationException::withMessages(['ingredient_ids' => 'Состав рецепта изменился. Обновите страницу и повторите сортировку.']);
            }
            foreach ($ids->values() as $position => $id) {
                DB::table('share_recipe_has_items')->where('share_recipe_id', $recipe->id)->where('id', $id)->update(['sort_order' => $position + 1]);
            }
        });
    }
}
