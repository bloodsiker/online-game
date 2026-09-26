<?php

declare(strict_types=1);

namespace App\Modules\Influence\Application\DTOs;

final readonly class MapInfluenceBonuses
{
    public function __construct(
        public float $gatheringSpeedPercent = 0,
        public float $bonusResourceChancePercent = 0,
        public float $monsterMoneyPercent = 0,
    ) {}
}
