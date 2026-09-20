<?php

declare(strict_types=1);

namespace App\Modules\Moderation\Application\Services;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Interface\Application\Services\PlayerEffectStateBroadcaster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Carbon\CarbonInterface;

class ChatMuteEffectService
{
    public const EFFECT_SLUG = 'chat_mute';

    public function __construct(
        private readonly PlayerEffectStateBroadcaster $effectStateBroadcaster,
    ) {}

    public function apply(Player $player, CarbonInterface $expiresAt): PlayerActiveEffect
    {
        $effect = Effect::query()->where('slug', self::EFFECT_SLUG)->firstOrFail();
        $activeEffect = PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->where('effect_id', $effect->id)
            ->whereNull('battle_id')
            ->first() ?? new PlayerActiveEffect;

        $activeEffect->fill([
            'player_id' => $player->id,
            'effect_id' => $effect->id,
            'battle_id' => null,
            'type' => null,
            'source_player_id' => null,
            'source_magic_skill_id' => null,
            'applied_at' => now(),
            'last_tick_at' => now(),
            'next_tick_at' => null,
            'expires_at' => $expiresAt,
            'stacks' => 0,
            'current_value' => null,
            'tick_remainder' => 0,
        ]);
        $activeEffect->save();
        $this->effectStateBroadcaster->broadcast($player);

        return $activeEffect;
    }

    public function remove(Player $player): void
    {
        $deleted = PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->whereNull('battle_id')
            ->whereHas('effect', fn ($query) => $query->where('slug', self::EFFECT_SLUG))
            ->delete();

        if ($deleted > 0) {
            $this->effectStateBroadcaster->broadcast($player);
        }
    }
}
