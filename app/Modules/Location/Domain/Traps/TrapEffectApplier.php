<?php

declare(strict_types=1);

namespace App\Modules\Location\Domain\Traps;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Interface\Application\Services\PlayerEffectStateBroadcaster;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

/** Накладывает на игрока вне боя временный эффект (в том числе периодический урон) от ловушки. */
final readonly class TrapEffectApplier
{
    public function __construct(
        private PlayerStatService $statService,
        private PlayerEffectStateBroadcaster $broadcaster,
    ) {}

    /** Перезаписывает действующий эффект (срок обновляется). false — эффекта с таким slug нет. */
    public function apply(Player $player, string $slug, int $seconds, int $perTickValue): bool
    {
        $effect = Effect::query()->where('slug', $slug)->first();
        if ($effect === null) {
            return false;
        }

        $active = PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->where('effect_id', $effect->id)
            ->whereNull('battle_id')
            ->first();

        ($active ?? new PlayerActiveEffect)->fill([
            'player_id' => $player->id, 'effect_id' => $effect->id, 'battle_id' => null,
            'type' => $effect->active_type?->value,
            'source_player_id' => null, 'source_magic_skill_id' => null,
            'applied_at' => now(), 'last_tick_at' => now(), 'next_tick_at' => null,
            'expires_at' => now()->addSeconds($seconds),
            'stacks' => 0, 'current_value' => $perTickValue, 'tick_remainder' => 0,
        ])->save();

        $this->statService->invalidate($player);
        $this->broadcaster->broadcast($player);

        return true;
    }
}
