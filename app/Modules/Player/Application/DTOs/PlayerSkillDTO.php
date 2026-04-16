<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\DTOs;

final readonly class PlayerSkillDTO
{
    public function __construct(
        public string $name,
        public int $level,
        public int $exp,
        public int $expUp,
    ) {}

    public function expPercent(): float
    {
        return $this->expUp > 0 ? round($this->exp * 100 / $this->expUp, 1) : 0.0;
    }
}