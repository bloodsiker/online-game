<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationDungeonSessionDTO
{
    public function __construct(
        public string $name,
        public bool $isSurvival,
        public bool $isTower,
        public ?int $currentWave,
        public ?int $waveCount,
        public ?int $currentStageNumber,
        public ?string $currentStageName,
        public bool $allCleared,
        public ?int $expiresAtTimestamp,
        public bool $canExit,
        public string $exitUrl,
    ) {}
}
