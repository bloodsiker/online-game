<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Application\DTOs;

final readonly class GambleExchangeResourceDTO
{
    /**
     * @param  list<GambleExchangeOptionDTO>  $options
     */
    public function __construct(
        public int $shareItemId,
        public string $name,
        public string $image,
        public string $rarityColor,
        public int $availableCount,
        public array $options,
    ) {}
}
