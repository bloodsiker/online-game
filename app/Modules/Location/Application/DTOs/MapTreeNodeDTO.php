<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class MapTreeNodeDTO
{
    /** @param list<MapTreeNodeDTO> $children */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public bool $isCurrent,
        public array $children,
    ) {}
}
