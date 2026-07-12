<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\DTOs;

final readonly class FightListItemDTO
{
    /**
     * @param  array<int, array{userId: int, name: string, level: int, alive: bool}>  $players
     * @param  array<int, array{locationMonsterId: int, name: string, level: int, alive: bool}>  $monsters
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $typeLabel,
        public string $startedAt,
        public int $rounds,
        public string $duration,
        public array $players,
        public array $monsters,
        public ?string $winnerLabel,
        public ?int $locationId,
        public ?string $locationName,
    ) {}
}
