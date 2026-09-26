<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\DTOs;

use Carbon\CarbonInterface;

final readonly class WorldEventCollectionResult
{
    public function __construct(
        public bool $allowed,
        public ?string $error = null,
        public int $playerProgress = 0,
        public int $playerLimit = 0,
        public int $influenceAwarded = 0,
        public int $mapInfluence = 0,
        public ?CarbonInterface $targetExpiresAt = null,
        public bool $stageAdvanced = false,
        public ?string $nextStageTitle = null,
    ) {}
}
