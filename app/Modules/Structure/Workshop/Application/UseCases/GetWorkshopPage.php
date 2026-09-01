<?php

declare(strict_types=1);

namespace App\Modules\Structure\Workshop\Application\UseCases;

use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\RecipeUnlockType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class GetWorkshopPage
{
    public function execute(User $user, int $structureId): array
    {
        $workshop = Structure::query()->findOrFail($structureId);
        abort_unless($workshop->isWorkshop(), 404);
        abort_unless((int) $workshop->location_id === (int) $user->location_id, 403);

        $resources = $this->resourceCounts($user);
        $skills = PlayerSkill::query()
            ->where('player_id', $user->player->id)
            ->pluck('lvl', 'skill_id');

        $recipes = ShareRecipe::query()
            ->where('unlock_type', RecipeUnlockType::LEARNABLE->value)
            ->whereHas('players', fn ($query) => $query->where('players.id', $user->player->id))
            ->whereHas('itemInfo.skill', fn ($query) => $query->where('type', 'peaceful'))
            ->whereNotNull('kraft_item_id')
            ->with(['itemInfo.skill', 'kraftItem', 'items'])
            ->orderBy('id')
            ->get()
            ->map(function (ShareRecipe $recipe) use ($resources, $skills): array {
                $requiredLevel = max(1, (int) $recipe->itemInfo->skill_lvl);
                $currentLevel = (int) ($skills[$recipe->itemInfo->skill_id] ?? 1);
                $ingredients = $recipe->items->map(function ($ingredient) use ($resources): array {
                    $available = (int) ($resources[$ingredient->id] ?? 0);
                    $required = (int) $ingredient->pivot->count;

                    return [
                        'id' => (int) $ingredient->id,
                        'name' => (string) $ingredient->name,
                        'image' => (string) $ingredient->image,
                        'available' => $available,
                        'required' => $required,
                        'enough' => $available >= $required,
                    ];
                })->values()->all();

                return [
                    'id' => (int) $recipe->id,
                    'name' => (string) $recipe->itemInfo->name,
                    'image' => (string) $recipe->itemInfo->image,
                    'resultName' => (string) $recipe->kraftItem->name,
                    'resultImage' => (string) $recipe->kraftItem->image,
                    'professionName' => (string) ($recipe->itemInfo->skill?->name ?? 'Профессия'),
                    'requiredLevel' => $requiredLevel,
                    'currentLevel' => $currentLevel,
                    'ingredients' => $ingredients,
                    'canCraft' => $currentLevel >= $requiredLevel && collect($ingredients)->every(fn ($item) => $item['enough']),
                ];
            })
            ->values()
            ->all();

        return ['workshop' => $workshop, 'recipes' => $recipes];
    }

    private function resourceCounts(User $user): array
    {
        return DB::table('backpacks')
            ->join('items', 'items.id', '=', 'backpacks.item_id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->groupBy('items.share_item_id')
            ->selectRaw('items.share_item_id, SUM(backpacks.count) as total')
            ->pluck('total', 'items.share_item_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
