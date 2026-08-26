<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerItemBuff;

class BuffAttackStrategy implements ItemEffectStrategyInterface
{
    private const DEFAULT_DURATION = 300; // 5 минут

    public function __construct(
        private readonly PlayerStatService $statService,
    ) {}

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

        $this->statService->invalidate($player);
    }
}
