<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationPageDTO
{
    /**
     * @param  list<LocationMonsterDTO>  $monsters
     * @param  list<LocationNpcDTO>  $npcs
     * @param  array<string, LocationMoveDirectionDTO>  $moves
     * @param  list<LocationStructureDTO>  $structures
     */
    public function __construct(
        public int $locationId,
        public string $name,
        public string $description,
        public ?LocationDungeonSessionDTO $dungeonSession,
        public bool $hasBattle,
        public ?string $battleUrl,
        public array $monsters,
        public array $npcs,
        public array $moves,
        public int $itemsOnLocationCount,
        public string $takeItemsUrl,
        public array $structures,
        public string $locationUsersJson,
        public LocationPlayerFrameDTO $playerFrame,
        public bool $hasClanMembership,
    ) {}
}
