<?php

declare(strict_types=1);

namespace App\Modules\Influence\Application\DTOs;

final readonly class MapInfluenceAwardResult
{
    /** @param list<string> $unlockedLevels */
    public function __construct(
        public int $awarded,
        public int $total,
        public array $unlockedLevels = [],
    ) {}
}
