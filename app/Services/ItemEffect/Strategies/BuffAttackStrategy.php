<?php

namespace App\Services\ItemEffect\Strategies;

use App\Models\Player\Player;
use App\ItemEffect\ValueObjects\ItemEffectValue;

class BuffAttackStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect): void
    {
        // Implementation for buffing attack goes here
    }
}
