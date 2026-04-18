<?php

namespace App\Services\ItemEffect\Strategies;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Services\ItemEffect\ValueObjects\ItemEffectValue;

class HealMpStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $cap = $mpMax ?? $player->mp_max;

        $amount = $effect->isPercent()
            ? (int) ($cap * $effect->value / 100)
            : $effect->value;

        $player->changeMp($amount, $cap);
    }
}
