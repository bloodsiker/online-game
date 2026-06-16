<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\DTOs;

final readonly class ActiveDungeonSessionDTO
{
    public function __construct(
        public int $dungeonId,
        public string $dungeonName,
        public ?int $expiresAtTimestamp,
    ) {}
}
