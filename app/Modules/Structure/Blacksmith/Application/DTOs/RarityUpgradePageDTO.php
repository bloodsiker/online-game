<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

final readonly class RarityUpgradePageDTO
{
    /** @param list<array<string, mixed>> $items */
    public function __construct(
        public Structure $blacksmith,
        public array $items,
        public string $itemTooltipScript,
    ) {}
}
