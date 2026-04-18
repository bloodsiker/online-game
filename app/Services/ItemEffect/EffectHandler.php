<?php

namespace App\Services\ItemEffect;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

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
