<?php

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

interface ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void;
}
