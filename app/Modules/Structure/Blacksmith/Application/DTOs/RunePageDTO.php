<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\Structure;

final readonly class RunePageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $runes
     * @param  array<int, array<string, mixed>>  $runeKeys
     */
    public function __construct(
        public Structure $blacksmith,
        public array $items,
        public array $runes,
        public array $runeKeys,
        public string $itemTooltipScript,
    ) {}
}
