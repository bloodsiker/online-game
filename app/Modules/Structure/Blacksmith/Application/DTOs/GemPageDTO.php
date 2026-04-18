<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

final readonly class GemPageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $gems
     * @param  array<int, array<string, mixed>>  $socketKits
     */
    public function __construct(
        public Structure $blacksmith,
        public array $items,
        public array $gems,
        public array $socketKits,
        public string $itemTooltipScript,
    ) {}
}
