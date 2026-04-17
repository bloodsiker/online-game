<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Item\Item;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\CraftItemDTO;
use Illuminate\Support\Facades\DB;

class CraftItem
{
    public function execute(CraftItemDTO $data): BlacksmithActionResultDTO
    {
        $recipeItem = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $data->user->id)
            ->where('backpacks.item_id', $data->recipeItemId)
            ->where('share_items.type', ShareItemType::RECIPE->value)
            ->firstOrFail();

        $resources = DB::table('share_items')
            ->select(['share_items.id', 'backpacks.count'])
            ->join('items', 'items.share_item_id', '=', 'share_items.id')
            ->join('backpacks', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $data->user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RESOURCE)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'count' => $item->count,
            ])
            ->toArray();

        $recipe = $recipeItem->item->itemInfo->recipe;
        $itemsNeedKraft = $recipe->items;

        foreach ($itemsNeedKraft as $itemNeedKraft) {
            $resourceCount = $itemNeedKraft->getCountItemPerRecipe($resources);

            if ($resourceCount < $itemNeedKraft->pivot->count) {
                return new BlacksmithActionResultDTO(false, 'Не достаточно ресурсов для крафта');
            }
        }

        return DB::transaction(function () use ($data, $recipeItem, $recipe, $itemsNeedKraft) {
            $percentKraft = mt_rand(0, 100);

            if ($percentKraft <= $recipe->percent) {
                $successKraftItem = new Item;
                $successKraftItem->share_item_id = $recipe->kraftItem->id;
                $successKraftItem->save();

                $data->user->backpack()->attach($successKraftItem->id, ['equipped' => 0, 'count' => 1]);

                $message = sprintf('Успешний крафт. Получено %s', $recipe->kraftItem->name);
            } else {
                $message = 'Не удачный крафт';
            }

            $recipeItem->item->delete();
            $recipeItem->delete();

            foreach ($itemsNeedKraft as $itemDelete) {
                $itemBackpack = Backpack::select('backpacks.*')
                    ->where(['items.share_item_id' => $itemDelete->id])
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->where('backpacks.user_id', $data->user->id)
                    ->first();

                if (! $itemBackpack instanceof Backpack) {
                    continue;
                }

                if ($itemBackpack->count > $itemDelete->pivot->count) {
                    $itemBackpack->count -= $itemDelete->pivot->count;
                    $itemBackpack->save();
                } else {
                    $itemBackpack->delete();
                    Item::where('id', $itemBackpack->item_id)->delete();
                }
            }

            return new BlacksmithActionResultDTO(true, $message, str_starts_with($message, 'Успеш'));
        });
    }
}
