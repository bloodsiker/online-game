<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\DTOs;

final readonly class PlayerHpRecalculation
{
    public function __construct(
        public string $name,
        public int $level,
        public int $oldHpMax,
        public int $newHpMax,
        public int $oldHpNow,
        public int $newHpNow,
    ) {}
}
