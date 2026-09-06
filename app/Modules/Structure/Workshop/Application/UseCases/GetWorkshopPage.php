<?php

declare(strict_types=1);

namespace App\Modules\Structure\Workshop\Application\UseCases;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\RecipeUnlockType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class GetWorkshopPage
{
    public function __construct(private readonly ItemTooltipCollector $tooltipCollector) {}

    /**
     * @param  list<string>  $skillNames  фильтр рецептов по именам профессий (пусто — все мирные).
     */
    public function execute(User $user, int $structureId, string $expectedStructureType = Structure::TYPE_WORKSHOP, array $skillNames = [], bool $learnedOnly = true): array
    {
        $workshop = Structure::query()->findOrFail($structureId);
        abort_unless($workshop->type === $expectedStructureType, 404);
        abort_unless((int) $workshop->location_id === (int) $user->location_id, 403);

        $resources = $this->resourceCounts($user);
        $skills = PlayerSkill::query()
            ->where('player_id', $user->player->id)
            ->pluck('lvl', 'skill_id');
        $learnedIds = DB::table('player_recipes')
            ->where('player_id', $user->player->id)
            ->pluck('share_recipe_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $recipeModels = ShareRecipe::query()
            ->where('unlock_type', RecipeUnlockType::LEARNABLE->value)
            ->when($learnedOnly, fn ($query) => $query->whereHas('players', fn ($q) => $q->where('players.id', $user->player->id)))
            ->whereHas('itemInfo.skill', function ($query) use ($skillNames): void {
                $query->where('type', 'peaceful');
                if ($skillNames !== []) {
                    $query->whereIn('name', $skillNames);
                }
            })
            ->whereNotNull('kraft_item_id')
            ->with(['itemInfo.skill', 'kraftItem', 'items'])
            ->orderBy('id')
            ->get();

        $recipes = $recipeModels
            ->map(function (ShareRecipe $recipe) use ($resources, $skills, $learnedIds): array {
                $requiredLevel = max(1, (int) $recipe->itemInfo->skill_lvl);
                $currentLevel = (int) ($skills[$recipe->itemInfo->skill_id] ?? 1);
                $learned = in_array((int) $recipe->id, $learnedIds, true);
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
                    'nameColor' => $recipe->itemInfo->rarity?->color() ?? '#333333',
                    'recipeItemId' => (int) $recipe->itemInfo->id,
                    'image' => (string) $recipe->itemInfo->image,
                    'resultId' => (int) $recipe->kraftItem->id,
                    'resultName' => (string) $recipe->kraftItem->name,
                    'resultImage' => (string) $recipe->kraftItem->image,
                    'professionName' => (string) ($recipe->itemInfo->skill?->name ?? 'Профессия'),
                    'requiredLevel' => $requiredLevel,
                    'currentLevel' => $currentLevel,
                    'learned' => $learned,
                    'ingredients' => $ingredients,
                    'canCraft' => $learned && $currentLevel >= $requiredLevel && collect($ingredients)->every(fn ($item) => $item['enough']),
                ];
            })
            ->values()
            ->all();

        $tooltipItems = $recipeModels
            ->flatMap(static fn (ShareRecipe $recipe) => $recipe->items->concat([$recipe->kraftItem]))
            ->filter()
            ->unique('id')
            ->values();

        $itemTooltipScript = $this->tooltipCollector
            ->collectFrom(new ShareItemTooltipStrategy($tooltipItems))
            ->renderScript();

        return ['workshop' => $workshop, 'recipes' => $recipes, 'itemTooltipScript' => $itemTooltipScript];
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
