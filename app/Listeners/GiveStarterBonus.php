<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\Item\Item;
use App\Models\ShareItem;

class GiveStarterBonus
{
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        $itemRecipe = ShareItem::where('name', 'Рецепт "Кнут Архангела"')->first();
        $item2 = new Item();
        $item2->share_item_id = $itemRecipe->id;
        $item2->save();

        $itemCristal = ShareItem::where('name', 'Кристалл')->first();
        $item3 = new Item();
        $item3->share_item_id = $itemCristal->id;
        $item3->save();

        $user->backpack()->attach($item2->id, ['equipped' => 0]);
        $user->backpack()->attach($item3->id, ['equipped' => 0, 'count' => 200]);
    }
}
