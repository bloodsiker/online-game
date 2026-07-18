<?php

declare(strict_types=1);

namespace App\Modules\Rating\Application\DTOs;

final readonly class RatingEntryDTO
{
    public function __construct(
        public int $position,
        public int $userId,
        public string $userName,
        public int $level,
        public bool $hasClan,
        public ?int $clanId,
        public ?string $clanName,
        public ?string $clanIconUrl,
        public int $value,
    ) {}
}
