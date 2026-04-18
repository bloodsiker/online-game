<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Mappers;

use App\Modules\Interface\Application\DTOs\HeroEffectDTO;
use App\Modules\Interface\Application\DTOs\HeroPageDTO;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Collection;

class HeroPageViewMapper
{
    public function map(Player $player, StatSheet $playerDecorator, Collection $activeEffects): HeroPageDTO
    {
        return new HeroPageDTO(
            player: $player,
            playerDecorator: $playerDecorator,
            activeEffects: $activeEffects->map(function ($activeEffect) {
                $name = $activeEffect->effect?->name
                    ?? ($activeEffect->type ? ucfirst($activeEffect->type->value) : 'Эффект');

                $remainingSeconds = $activeEffect->expires_at
                    ? (int) now()->diffInSeconds($activeEffect->expires_at, false)
                    : 0;

                $isCurse = $activeEffect->type?->isDoT() || $activeEffect->type?->isStun()
                    || ($activeEffect->effect && in_array($activeEffect->effect->type, ['debuff']));

                return new HeroEffectDTO(
                    id: ($activeEffect->effect?->slug ?? 'effect').'_'.$activeEffect->id,
                    name: $name,
                    duration: max(0, $remainingSeconds),
                    isCurse: $isCurse,
                );
            })->all(),
        );
    }
}
