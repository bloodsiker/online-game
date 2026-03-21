<?php

namespace App\Services\ItemEffect\Strategies;

use App\Models\Player\Player;
use App\Services\ItemEffect\ValueObjects\ItemEffectValue;

class HealHpStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect, int $hpMax = null, int $mpMax = null): void
    {
        $cap = $hpMax ?? $player->hp_max;

        $amount = $effect->isPercent()
            ? (int)($cap * $effect->value / 100)
            : $effect->value;

        $player->changeHp($amount, $cap);
    }
}
