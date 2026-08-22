<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Battle\Domain\Enums\ActiveEffectType;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Player\Domain\DTO\PlayerEffectTickResult;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Carbon\CarbonInterface;

final class PlayerTimedEffectService
{
    /**
     * Applies every elapsed real-time DoT tick exactly once.
     *
     * The caller must lock the player row inside a transaction. Active effect
     * rows are locked here, so a battle action and a heartbeat cannot consume
     * the same poison tick twice.
     */
    public function process(
        Player $player,
        CarbonInterface $now,
        int $minimumHp = 0,
    ): PlayerEffectTickResult {
        $effects = PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->whereIn('type', $this->damageEffectTypes())
            ->where(fn ($query) => $query
                ->whereNull('battle_id')
                ->orWhereHas('battle', fn ($battleQuery) => $battleQuery
                    ->where('status', BattleStatus::ACTIVE)))
            ->with('effect')
            ->lockForUpdate()
            ->get();

        $totalDamage = 0;
        $processedEffects = [];
        $effectsChanged = false;
        $fallbackTickSeconds = max(1, (int) config('game.player_heartbeat_seconds', 10));

        foreach ($effects as $activeEffect) {
            $lastTickAt = $activeEffect->last_tick_at
                ?? $activeEffect->applied_at
                ?? $activeEffect->created_at;

            if ($lastTickAt === null) {
                $activeEffect->last_tick_at = $now;
                $activeEffect->save();
                $effectsChanged = true;

                continue;
            }

            $tickUntil = $activeEffect->expires_at !== null && $activeEffect->expires_at->lt($now)
                ? $activeEffect->expires_at
                : $now;
            $tickSeconds = max(1, (int) ($activeEffect->effect?->tick_interval ?: $fallbackTickSeconds));
            $elapsedSeconds = max(0, (int) $lastTickAt->diffInSeconds($tickUntil));
            $dueTicks = intdiv($elapsedSeconds, $tickSeconds);

            if ($activeEffect->battle_id !== null) {
                $dueTicks = min($dueTicks, max(0, (int) $activeEffect->stacks));
            }

            if ($dueTicks > 0) {
                $damage = max(0, (int) $activeEffect->current_value) * $dueTicks;
                $totalDamage += $damage;

                $processedEffects[] = [
                    'label' => $activeEffect->type?->label() ?? 'Периодический эффект',
                    'emoji' => $activeEffect->type?->emoji() ?? '',
                    'damage' => $damage,
                    'ticks' => $dueTicks,
                ];

                $activeEffect->last_tick_at = $lastTickAt->copy()->addSeconds($dueTicks * $tickSeconds);

                if ($activeEffect->battle_id !== null) {
                    $activeEffect->stacks = max(0, (int) $activeEffect->stacks - $dueTicks);
                }

                $effectsChanged = true;
            }

            $isExhaustedBattleEffect = $activeEffect->battle_id !== null
                && (int) $activeEffect->stacks <= 0;
            $isExpiredTimedEffect = $activeEffect->expires_at !== null
                && $activeEffect->expires_at->lte($now);

            if ($isExhaustedBattleEffect || $isExpiredTimedEffect) {
                $activeEffect->delete();
                $effectsChanged = true;
            } elseif ($dueTicks > 0) {
                $activeEffect->save();
            }
        }

        if ($totalDamage > 0 && (int) $player->hp_now > 0) {
            $player->hp_now = max($minimumHp, (int) $player->hp_now - $totalDamage);
            $player->save();
        }

        return new PlayerEffectTickResult($totalDamage, $processedEffects, $effectsChanged);
    }

    /** @return list<string> */
    private function damageEffectTypes(): array
    {
        return array_values(array_map(
            static fn (ActiveEffectType $type): string => $type->value,
            array_filter(ActiveEffectType::cases(), static fn (ActiveEffectType $type): bool => $type->isDoT()),
        ));
    }
}
