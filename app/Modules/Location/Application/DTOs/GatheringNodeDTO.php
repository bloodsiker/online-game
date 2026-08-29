<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class GatheringNodeDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $icon,
        public int $gatherTime,
        public int $respawnTime,
        public int $successPercent,
        public int $requiredLevel,
        public int $experience,
    ) {}
}
