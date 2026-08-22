<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\DTO;

final readonly class PlayerEffectTickResult
{
    /**
     * @param  list<array{label: string, emoji: string, damage: int, ticks: int}>  $effects
     */
    public function __construct(
        public int $totalDamage,
        public array $effects,
        public bool $effectsChanged,
    ) {}
}
