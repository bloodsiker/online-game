<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\DTOs;

class InitialExperienceDTO
{
    public function __construct(
        public readonly int $expUp,
        public readonly int $expDiff,
    ) {}
}
