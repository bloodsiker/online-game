<?php

namespace App\Services\ItemEffect\Strategies;

use App\ItemEffect\ValueObjects\ItemEffectValue;
use App\Models\Player\Player;

interface ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect): void;
}
