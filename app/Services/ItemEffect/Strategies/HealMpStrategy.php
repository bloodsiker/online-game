<?php

namespace App\Services\ItemEffect\Strategies;

use App\ItemEffect\ValueObjects\ItemEffectValue;
use App\Models\Player\Player;

class HealMpStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect): void
    {
        $amount = $effect->isPercent()
            ? (int)($player->max_mp * $effect->value / 100)
            : $effect->value;

        $player->changeMp($amount);
    }
}
