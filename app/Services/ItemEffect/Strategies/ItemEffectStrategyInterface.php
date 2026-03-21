<?php

namespace App\Services\ItemEffect\Strategies;

use App\Services\ItemEffect\ValueObjects\ItemEffectValue;
use App\Models\Player\Player;

interface ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect, int $hpMax = null, int $mpMax = null): void;
}
