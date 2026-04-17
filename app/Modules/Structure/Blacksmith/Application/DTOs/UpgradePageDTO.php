<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\Structure;
use Illuminate\Database\Eloquent\Collection;

final readonly class UpgradePageDTO
{
    /**
     * @param  Collection<int, mixed>  $items
     * @param  Collection<int, mixed>  $baseScrolls
     * @param  Collection<int, mixed>  $bonusScrolls
     */
    public function __construct(
        public Structure $blacksmith,
        public mixed $player,
        public mixed $playerDecorator,
        public Collection $items,
        public Collection $baseScrolls,
        public Collection $bonusScrolls,
        public string $itemTooltipScript,
    ) {}
}
