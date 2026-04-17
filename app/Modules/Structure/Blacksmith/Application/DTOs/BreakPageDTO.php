<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\Share\ShareItem;
use App\Models\Structure;
use Illuminate\Database\Eloquent\Collection;

final readonly class BreakPageDTO
{
    /**
     * @param  Collection<int, mixed>  $items
     */
    public function __construct(
        public Structure $blacksmith,
        public Collection $items,
        public ShareItem $crystal,
        public string $itemTooltipScript,
    ) {}
}
