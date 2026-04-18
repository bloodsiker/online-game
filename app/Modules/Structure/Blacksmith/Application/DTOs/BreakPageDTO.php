<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

final readonly class BreakPageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        public Structure $blacksmith,
        public array $items,
        public ShareItem $crystal,
        public string $itemTooltipScript,
    ) {}
}
