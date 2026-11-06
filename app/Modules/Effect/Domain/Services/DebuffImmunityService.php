<?php

declare(strict_types=1);

namespace App\Modules\Effect\Domain\Services;

use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

/**
 * Снятие дебаффа противоядием и временный иммунитет к его повторному наложению.
 *
 * Иммунитет хранится как обычный активный эффект игрока, ссылающийся на
 * эффект-маркер со слагом immunity_<тип> (стат-модификаторов у него нет,
 * поэтому на статы он не влияет, но виден в рамке эффектов игрока).
 */
final class DebuffImmunityService
{
    public static function slugFor(ActiveEffectType $type): string
    {
        return 'immunity_'.$type->value;
    }

    public function isImmune(int $playerId, ActiveEffectType $type): bool
    {
        return PlayerActiveEffect::query()
            ->where('player_id', $playerId)
            ->whereHas('effect', fn ($query) => $query->where('slug', self::slugFor($type)))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    /**
     * Снимает с игрока все активные дебаффы указанного типа. Эффекты из таблицы
     * effects снимаются только с флагом «Можно снять»; кастомные эффекты механик
     * боссов (без effect_id) снимаются всегда.
     */
    public function cure(int $playerId, ActiveEffectType $type): int
    {
        return PlayerActiveEffect::query()
            ->where('player_id', $playerId)
            ->where('type', $type)
            ->where(fn ($query) => $query
                ->whereNull('effect_id')
                ->orWhereHas('effect', fn ($effect) => $effect->where('is_dispellable', true)))
            ->delete();
    }

    public function grant(int $playerId, ActiveEffectType $type, int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        $marker = Effect::query()->where('slug', self::slugFor($type))->first();
        if ($marker === null) {
            return;
        }

        $existing = PlayerActiveEffect::query()
            ->where('player_id', $playerId)
            ->where('effect_id', $marker->id)
            ->first();

        $expiresAt = now()->addSeconds($seconds);

        if ($existing !== null) {
            if ($existing->expires_at === null || $existing->expires_at->lt($expiresAt)) {
                $existing->expires_at = $expiresAt;
                $existing->applied_at = now();
                $existing->save();
            }

            return;
        }

        PlayerActiveEffect::create([
            'player_id' => $playerId,
            'effect_id' => $marker->id,
            'battle_id' => null,
            'type' => null,
            'applied_at' => now(),
            'last_tick_at' => now(),
            'expires_at' => $expiresAt,
            'stacks' => 0,
            'current_value' => 0,
            'tick_remainder' => 0,
        ]);
    }
}
