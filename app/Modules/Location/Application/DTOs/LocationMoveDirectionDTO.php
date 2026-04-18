<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationMoveDirectionDTO
{
    public function __construct(
        public bool $available,
        public ?string $url,
        public string $label,
        public ?string $elementId = null,
    ) {}
}
