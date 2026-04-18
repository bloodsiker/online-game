<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationStructureActionDTO
{
    public function __construct(
        public string $label,
        public string $url,
    ) {}
}
