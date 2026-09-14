<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Application\DTOs;

final readonly class GambleExchangeOptionDTO
{
    public function __construct(
        public int $id,
        public int $resourceCost,
        public int $successChance,
        public int $rewardPoints,
        public bool $canAfford,
    ) {}
}
