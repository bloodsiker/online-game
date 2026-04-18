<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationResultDTO
{
    public function __construct(
        public ?LocationFightDTO $fight,
        public ?LocationPageDTO $page,
    ) {}
}
