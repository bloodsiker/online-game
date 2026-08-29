<?php

declare(strict_types=1);

namespace App\Modules\Structure\Workshop\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Workshop\Application\DTOs\WorkshopResultDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class LearnRecipe
{
    public function execute(User $user, int $shareItemId): WorkshopResultDTO
    {
        $recipe = ShareRecipe::query()->where('share_item_id', $shareItemId)->with('itemInfo.skill')->first();
        if ($recipe === null || $recipe->itemInfo?->type !== ShareItemType::RECIPE) {
            return new WorkshopResultDTO(false, 'Это не книга рецепта.', 422);
        }
        if ($recipe->itemInfo->skill === null || $recipe->itemInfo->skill->type !== 'peaceful') {
            return new WorkshopResultDTO(false, 'У рецепта не настроена мирная профессия.', 422);
        }

        try {
            return DB::transaction(function () use ($user, $recipe): WorkshopResultDTO {
                $player = Player::query()->whereKey($user->player->id)->lockForUpdate()->first();
                if ($player === null) {
                    return new WorkshopResultDTO(false, 'Игрок не найден.', 404);
                }

                if (DB::table('player_recipes')->where('player_id', $player->id)->where('share_recipe_id', $recipe->id)->exists()) {
                    return new WorkshopResultDTO(false, 'Этот рецепт уже изучен.', 422);
                }

                $backpackItem = Backpack::query()
                    ->where('user_id', $user->id)
                    ->where('equipped', 0)
                    ->whereHas('item', fn ($query) => $query->where('share_item_id', $recipe->share_item_id))
                    ->lockForUpdate()
                    ->first();
                if ($backpackItem === null || $backpackItem->count < 1) {
                    return new WorkshopResultDTO(false, 'У вас нет этой книги рецепта.', 422);
                }

                DB::table('player_recipes')->insert([
                    'player_id' => $player->id,
                    'share_recipe_id' => $recipe->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($backpackItem->count > 1) {
                    $backpackItem->decrement('count');
                } else {
                    $itemId = $backpackItem->item_id;
                    $backpackItem->delete();
                    if (! Backpack::query()->where('item_id', $itemId)->exists()) {
                        Item::query()->whereKey($itemId)->delete();
                    }
                }

                return new WorkshopResultDTO(true, sprintf('Изучен рецепт: «%s».', $recipe->itemInfo->name));
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            return new WorkshopResultDTO(false, 'Этот рецепт уже изучен.', 422);
        }
    }
}
