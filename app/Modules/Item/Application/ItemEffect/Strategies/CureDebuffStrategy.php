<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Effect\Domain\Services\DebuffImmunityService;
use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

/**
 * Противоядие: снимает дебафф своего типа, затем даёт иммунитет к нему на
 * duration_seconds и лечит value % от максимального HP (оба опциональны).
 */
class CureDebuffStrategy implements ItemEffectStrategyInterface
{
    public function __construct(
        private readonly DebuffImmunityService $immunity,
        private readonly PlayerStatService $statService,
    ) {}

    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $type = $effect->type->curedEffectType();
        if ($type === null) {
            return;
        }

        $this->immunity->cure((int) $player->id, $type);
        $this->immunity->grant((int) $player->id, $type, (int) ($effect->durationSeconds ?? 0));

        if ($effect->value > 0) {
            $cap = $hpMax ?? $player->hp_max;
            $amount = $effect->isPercent() ? (int) ($cap * $effect->value / 100) : $effect->value;
            $player->changeHp($amount, $cap);
        }

        $this->statService->invalidate($player);
    }
}
