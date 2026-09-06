<?php

declare(strict_types=1);

namespace App\Modules\Structure\Workshop\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\Services\PeacefulProfessionExperienceService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\RecipeUnlockType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Workshop\Application\DTOs\WorkshopResultDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class CraftProfessionItem
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly PeacefulProfessionExperienceService $professionExperienceService,
    ) {}

    public function execute(User $user, int $structureId, int $recipeId, string $expectedStructureType = Structure::TYPE_WORKSHOP): WorkshopResultDTO
    {
        return DB::transaction(function () use ($user, $structureId, $recipeId, $expectedStructureType): WorkshopResultDTO {
            $workshop = Structure::query()->whereKey($structureId)->lockForUpdate()->first();
            if ($workshop === null || $workshop->type !== $expectedStructureType) {
                return new WorkshopResultDTO(false, 'Мастерская не найдена.', 404);
            }
            if ((int) $workshop->location_id !== (int) $user->location_id) {
                return new WorkshopResultDTO(false, 'Для крафта нужно находиться в этой мастерской.', 403);
            }

            $player = Player::query()->whereKey($user->player->id)->lockForUpdate()->firstOrFail();
            $recipe = ShareRecipe::query()->whereKey($recipeId)->with(['itemInfo.skill', 'kraftItem', 'items'])->first();
            if ($recipe === null || $recipe->kraftItem === null) {
                return new WorkshopResultDTO(false, 'Рецепт не найден.', 404);
            }
            if ($recipe->unlock_type !== RecipeUnlockType::LEARNABLE) {
                return new WorkshopResultDTO(false, 'Одноразовый рецепт нельзя использовать в мастерской.', 422);
            }
            if ($recipe->itemInfo->skill === null || $recipe->itemInfo->skill->type !== 'peaceful') {
                return new WorkshopResultDTO(false, 'У рецепта не настроена мирная профессия.', 422);
            }
            if (! DB::table('player_recipes')->where('player_id', $player->id)->where('share_recipe_id', $recipe->id)->exists()) {
                return new WorkshopResultDTO(false, 'Этот рецепт ещё не изучен.', 422);
            }

            $requiredLevel = max(1, (int) $recipe->itemInfo->skill_lvl);
            $currentLevel = (int) (PlayerSkill::query()
                ->where('player_id', $player->id)
                ->where('skill_id', $recipe->itemInfo->skill_id)
                ->value('lvl') ?? 1);
            if ($currentLevel < $requiredLevel) {
                return new WorkshopResultDTO(false, sprintf('Требуется %s %d уровня.', $recipe->itemInfo->skill?->name ?? 'профессия', $requiredLevel), 422);
            }

            $lockedIngredients = [];
            foreach ($recipe->items as $ingredient) {
                $rows = Backpack::query()
                    ->where('user_id', $user->id)
                    ->where('equipped', 0)
                    ->whereHas('item', fn ($query) => $query->where('share_item_id', $ingredient->id))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
                $required = (int) $ingredient->pivot->count;
                if ($rows->sum('count') < $required) {
                    return new WorkshopResultDTO(false, sprintf('Не хватает ресурса «%s».', $ingredient->name), 422);
                }
                $lockedIngredients[] = [$rows, $required];
            }

            foreach ($lockedIngredients as [$rows, $required]) {
                $remaining = $required;
                foreach ($rows as $row) {
                    $take = min($remaining, (int) $row->count);
                    if ($take === (int) $row->count) {
                        $itemId = $row->item_id;
                        $row->delete();
                        if (! Backpack::query()->where('item_id', $itemId)->exists()) {
                            Item::query()->whereKey($itemId)->delete();
                        }
                    } else {
                        $row->decrement('count', $take);
                    }
                    $remaining -= $take;
                    if ($remaining === 0) {
                        break;
                    }
                }
            }

            $this->backpackService->addItemByShareItem($user, $recipe->kraftItem, 1);
            $experience = max(1, (int) $recipe->itemInfo->skill_exp);
            $this->professionExperienceService->award($player, $recipe->itemInfo->skill, $experience);

            return new WorkshopResultDTO(true, sprintf(
                'Создано: «%s» ×1. %s: опыт +%d.',
                $recipe->kraftItem->name,
                $recipe->itemInfo->skill->name,
                $experience,
            ));
        });
    }
}
