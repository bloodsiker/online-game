<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class GatheringPageDTO
{
    public function __construct(
        public int $locationId,
        public string $locationName,
        public string $locationDescription,
        public ?string $locationImage,
        public int $mapId,
        public string $mapName,
        public bool $enabled,
        public ?string $message,
        public array $professions,
        public array $nodes,
        public ?array $activeAttempt,
        public string $backUrl,
        public string $stateUrl,
        public string $startUrl,
        public string $completeUrl,
        public string $cancelUrl,
    ) {}
}
