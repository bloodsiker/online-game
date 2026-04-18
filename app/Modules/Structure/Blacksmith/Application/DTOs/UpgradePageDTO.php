<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

final readonly class UpgradePageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $baseScrolls
     * @param  array<int, array<string, mixed>>  $bonusScrolls
     */
    public function __construct(
        public Structure $blacksmith,
        public mixed $player,
        public mixed $playerDecorator,
        public array $items,
        public array $baseScrolls,
        public array $bonusScrolls,
        public string $itemTooltipScript,
    ) {}
}
