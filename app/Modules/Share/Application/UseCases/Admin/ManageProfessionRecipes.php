<?php

declare(strict_types=1);

namespace App\Modules\Share\Application\UseCases\Admin;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageProfessionRecipes
{
    public function get(Skill $skill): Collection
    {
        return $this->query($skill)->with(['itemInfo', 'kraftItem'])->displayOrder()->get();
    }

    /** @param Collection<int, int> $ids */
    public function reorder(Skill $skill, Collection $ids): void
    {
        DB::transaction(function () use ($skill, $ids): void {
            Skill::whereKey($skill->id)->lockForUpdate()->firstOrFail();
            $rows = $this->query($skill)->orderBy('id')->lockForUpdate()->get();
            if ($ids->sort()->values()->all() !== $rows->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values()->all()) {
                throw ValidationException::withMessages(['recipe_ids' => 'Список рецептов изменился. Обновите страницу и повторите сортировку.']);
            }
            foreach ($ids->values() as $position => $id) {
                DB::table('share_recipes')->where('id', $id)->update(['sort_order' => $position + 1]);
            }
        });
    }

    private function query(Skill $skill): Builder
    {
        return ShareRecipe::query()->whereHas('itemInfo', fn ($query) => $query->where('skill_id', $skill->id));
    }
}
