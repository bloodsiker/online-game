<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationStructureDTO
{
    /**
     * @param  list<LocationStructureActionDTO>  $actions
     */
    public function __construct(
        public string $name,
        public string $entryUrl,
        public array $actions,
    ) {}
}
