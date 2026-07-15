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
        public int $expDiff,
    ) {}

    public function expPercent(): float
    {
        if ($this->expDiff <= 0) {
            return 0.0;
        }

        $levelStartExp = max(0, $this->expUp - $this->expDiff);
        $levelExp = max(0, $this->exp - $levelStartExp);

        return min(round($levelExp * 100 / $this->expDiff, 1), 100.0);
    }
}
