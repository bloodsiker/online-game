<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Mappers;

use App\Modules\Interface\Application\DTOs\HeroEffectDTO;
use App\Modules\Interface\Application\DTOs\HeroPageDTO;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class HeroPageViewMapper
{
    public function map(Player $player, StatSheet $playerDecorator, Collection $activeEffects): HeroPageDTO
    {
        return new HeroPageDTO(
            player: $player,
            playerDecorator: $playerDecorator,
            activeEffects: $this->mapEffects($activeEffects, now()),
        );
    }

    /** @return list<HeroEffectDTO> */
    public function mapEffects(Collection $activeEffects, CarbonInterface $now): array
    {
        return $activeEffects->map(function ($activeEffect) use ($now): HeroEffectDTO {
            $name = $activeEffect->effect?->name
                ?? ($activeEffect->type?->label() ?? 'Эффект');

            $remainingSeconds = $activeEffect->expires_at
                ? (int) $now->diffInSeconds($activeEffect->expires_at, false)
                : $this->remainingBattleEffectSeconds($activeEffect, $now);

            $isCurse = $activeEffect->type?->isDoT() || $activeEffect->type?->isStun()
                || ($activeEffect->effect && in_array($activeEffect->effect->type, ['debuff']));

            return new HeroEffectDTO(
                id: ($activeEffect->effect?->slug ?? 'effect').'_'.$activeEffect->id,
                name: $name,
                duration: max(0, $remainingSeconds),
                isCurse: $isCurse,
            );
        })->values()->all();
    }

    private function remainingBattleEffectSeconds(mixed $activeEffect, CarbonInterface $now): int
    {
        if ($activeEffect->battle_id === null || ! $activeEffect->type?->isDoT()) {
            return 0;
        }

        $tickSeconds = max(1, (int) ($activeEffect->effect?->tick_interval
            ?: config('game.player_heartbeat_seconds', 10)));
        $lastTickAt = $activeEffect->last_tick_at ?? $activeEffect->applied_at ?? $now;
        $elapsed = max(0, (int) $lastTickAt->diffInSeconds($now));
        $untilNextTick = max(0, $tickSeconds - min($tickSeconds, $elapsed));

        return $untilNextTick + max(0, (int) $activeEffect->stacks - 1) * $tickSeconds;
    }
}
