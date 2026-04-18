<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationMonsterDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isBoss,
        public string $infoUrl,
        public string $attackUrl,
    ) {}
}
