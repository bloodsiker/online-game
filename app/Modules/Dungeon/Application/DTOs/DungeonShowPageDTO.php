<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\DTOs;

final readonly class DungeonShowPageDTO
{
    public function __construct(
        public DungeonViewDTO $dungeon,
        public ?ActiveDungeonSessionDTO $activeSession,
    ) {}
}
