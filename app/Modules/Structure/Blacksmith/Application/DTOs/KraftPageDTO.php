<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

final readonly class KraftPageDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $recipes
     * @param  array<int, array{id:int, count:int}>  $resources
     * @param  array<int, array<string, mixed>>  $assemblies
     */
    public function __construct(
        public Structure $blacksmith,
        public array $recipes,
        public array $resources,
        public string $itemTooltipScript,
        public array $assemblies = [],
    ) {}
}
