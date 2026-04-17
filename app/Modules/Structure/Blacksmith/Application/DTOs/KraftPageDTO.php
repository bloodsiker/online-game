<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\Structure;

final readonly class KraftPageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $recipes
     * @param  array<int, array{id:int, count:int}>  $resources
     */
    public function __construct(
        public Structure $blacksmith,
        public array $recipes,
        public array $resources,
        public string $itemTooltipScript,
    ) {}
}
