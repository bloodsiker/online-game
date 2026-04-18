<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class ChestEntryDTO
{
    public function __construct(
        public string $image,
        public string $name,
        public int $count,
        public string $pickupUrl,
    ) {}
}
