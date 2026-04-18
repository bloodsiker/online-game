<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\DTOs;

final readonly class HeroEffectDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public int $duration,
        public bool $isCurse,
    ) {}
}
