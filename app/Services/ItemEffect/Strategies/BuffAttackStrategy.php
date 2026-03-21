<?php

namespace App\Services\ItemEffect\Strategies;

use App\Models\Player\Player;
use App\Services\ItemEffect\ValueObjects\ItemEffectValue;

class BuffAttackStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect, int $hpMax = null, int $mpMax = null): void
    {
        // Implementation for buffing attack goes here
    }
}
