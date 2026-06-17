<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\DTOs;

final readonly class DungeonViewDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public int $tier,
        public int $maxPlayers,
        public int $minLevel,
        public int $cooldownSeconds,
        public ?string $cooldownType,
        public ?int $timeLimitSeconds,
        public bool $requiresKey,
        public ?string $entryKeyName,
        public ?int $entryLocationId,
        public string $deathBehavior,
        public string $deathBehaviorLabel,
        public ?int $deathReturnLocationId,
        public bool $monsterRespawn,
    ) {}
}
