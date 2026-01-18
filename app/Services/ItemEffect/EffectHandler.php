<?php

namespace App\Services\ItemEffect;

use App\Models\Item\Item;
use App\Models\Player\Player;

class EffectHandler
{
    public function apply(Player $player, Item $item): void
    {
        foreach ($item->itemInfo->effects as $effectModel) {
            $effect = $effectModel->toValueObject();

            $strategy = ItemEffectStrategyFactory::make($effect->type);
            $strategy->apply($player, $effect);
        }
    }
}
