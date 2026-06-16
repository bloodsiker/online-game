<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\DTOs;

final readonly class DungeonIndexPageDTO
{
    /**
     * @param  array<int, DungeonViewDTO>  $dungeons
     */
    public function __construct(
        public array $dungeons,
        public ?ActiveDungeonSessionDTO $activeSession,
    ) {}
}
