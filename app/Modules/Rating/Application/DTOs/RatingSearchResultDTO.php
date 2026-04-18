<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\DTOs;

final readonly class RatingSearchResultDTO
{
    public function __construct(
        public bool $ok,
        public ?int $page = null,
        public ?int $position = null,
        public ?string $error = null,
    ) {}
}
