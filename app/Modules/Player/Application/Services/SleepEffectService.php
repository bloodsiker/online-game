<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\Services;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Interface\Application\Services\PlayerEffectStateBroadcaster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Carbon\CarbonInterface;

/**
 * Эффект «Сон» (слот эффектов в герое). Источник истины — players.sleep_until,
 * эффект только показывает сон и сам удаляется по истечении (как «Молчание»).
 */
final class SleepEffectService
{
    public const EFFECT_SLUG = 'sleep';

    public function __construct(private readonly PlayerEffectStateBroadcaster $broadcaster) {}

    public function apply(Player $player, CarbonInterface $expiresAt): void
    {
        $effect = Effect::query()->where('slug', self::EFFECT_SLUG)->first();
        if ($effect === null) {
            return;
        }

        $active = PlayerActiveEffect::query()
            ->where('player_id', $player->id)->where('effect_id', $effect->id)->whereNull('battle_id')
            ->first() ?? new PlayerActiveEffect;
        $active->fill([
            'player_id' => $player->id, 'effect_id' => $effect->id, 'battle_id' => null, 'type' => null,
            'source_player_id' => null, 'source_magic_skill_id' => null,
            'applied_at' => now(), 'last_tick_at' => now(), 'next_tick_at' => null, 'expires_at' => $expiresAt,
            'stacks' => 0, 'current_value' => null, 'tick_remainder' => 0,
        ])->save();
        $this->broadcaster->broadcast($player);
    }
}
