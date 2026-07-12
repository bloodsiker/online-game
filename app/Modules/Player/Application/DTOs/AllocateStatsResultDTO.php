<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\DTOs;

final readonly class AllocateStatsResultDTO
{
    public function __construct(
        public int $freeStats,
        public int $strength,
        public int $intuition,
        public int $agility,
        public int $intelligence,
        public int $wisdom,
        public int $endurance,
    ) {}
}
