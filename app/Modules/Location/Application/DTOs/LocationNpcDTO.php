<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationNpcDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $infoUrl,
        public string $talkUrl,
    ) {}
}
