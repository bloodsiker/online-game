<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Application\DTOs;

final readonly class ReputationExchangeViewItemDTO
{
    public function __construct(
        public int $shareItemId,
        public string $name,
        public string $image,
        public string $rarityColor,
        public int $points,
        public int $minReputation,
        public int $maxReputation,
        public int $availableCount,
        public bool $isCurrentBracket,
    ) {}
}
