<?php

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class HealHpStrategy implements ItemEffectStrategyInterface
{
    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $cap = $hpMax ?? $player->hp_max;

        $amount = $effect->isPercent()
            ? (int) ($cap * $effect->value / 100)
            : $effect->value;

        $player->changeHp($amount, $cap);
    }
}
