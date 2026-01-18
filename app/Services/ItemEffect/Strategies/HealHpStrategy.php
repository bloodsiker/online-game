<?php

namespace App\Services\ItemEffect\Strategies;

use App\Models\Player\Player;
use App\ItemEffect\ValueObjects\ItemEffectValue;

class HealHpStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect): void
    {
        $amount = $effect->isPercent()
            ? (int)($player->max_hp * $effect->value / 100)
            : $effect->value;

        $player->changeHp($amount);
    }
}
