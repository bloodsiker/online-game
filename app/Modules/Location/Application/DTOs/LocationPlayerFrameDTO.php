<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class LocationPlayerFrameDTO
{
    public function __construct(
        public int $hpCurrent,
        public int $hpMax,
        public int $mpCurrent,
        public int $mpMax,
        public float $experience,
        public int $level,
    ) {}
}
