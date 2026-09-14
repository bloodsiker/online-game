<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Application\DTOs;

final readonly class GambleExchangePageDTO
{
    /**
     * @param  list<GambleExchangeResourceDTO>  $resources
     */
    public function __construct(
        public int $structureId,
        public string $reputationName,
        public int $currentPoints,
        public array $resources,
        public ?string $offeringCooldown,
    ) {}
}
