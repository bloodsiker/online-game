<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\DTOs;

final readonly class WhoUserDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public int $lvl,
        public string $time,
        public bool $isOnline,
        public ?string $clanName,
        public ?string $clanIcon,
        public ?int $clanId,
    ) {}
}
