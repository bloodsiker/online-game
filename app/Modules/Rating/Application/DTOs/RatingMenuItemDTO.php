<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\DTOs;

final readonly class RatingMenuItemDTO
{
    public function __construct(
        public string $key,
        public string $title,
        public string $name,
        public bool $isActive,
        public bool $isSkill,
    ) {}
}
