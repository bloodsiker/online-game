<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class HandOverCandidateDTO
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $url,
    ) {}
}
