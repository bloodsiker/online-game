<?php

declare(strict_types=1);

namespace App\Services\ItemEffect\Strategies;

use App\Models\Player\Player;
use App\Models\Player\PlayerItemBuff;
use App\Services\ItemEffect\ValueObjects\ItemEffectValue;

class BuffAttackStrategy implements ItemEffectStrategyInterface
{
    private const DEFAULT_DURATION = 300; // 5 минут

    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $duration = $effect->durationSeconds ?? self::DEFAULT_DURATION;

        PlayerItemBuff::create([
            'player_id' => $player->id,
            'effect_type' => $effect->type,
            'value' => $effect->value,
            'value_type' => $effect->valueType,
            'expires_at' => now()->addSeconds($duration),
        ]);
    }
}
