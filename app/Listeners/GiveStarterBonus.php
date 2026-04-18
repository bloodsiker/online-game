<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\Share\ShareItem;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;

class GiveStarterBonus
{
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        $itemRecipe = ShareItem::where('name', 'Рецепт "Кнут Архангела"')->first();
        $item2 = new Item;
        $item2->share_item_id = $itemRecipe->id;
        $item2->save();

        $itemCristal = ShareItem::where('name', 'Кристалл')->first();
        $item3 = new Item;
        $item3->share_item_id = $itemCristal->id;
        $item3->save();

        $user->backpack()->attach($item2->id, ['equipped' => false]);
        $user->backpack()->attach($item3->id, ['equipped' => false, 'count' => 200]);
    }
}
