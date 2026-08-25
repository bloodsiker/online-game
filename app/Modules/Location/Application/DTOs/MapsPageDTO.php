<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class MapsPageDTO
{
    /** @param list<MapTreeNodeDTO> $roots */
    public function __construct(
        public array $roots,
        public int $mapsCount,
        public int $locationsCount,
    ) {}
}
