<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\DTOs;

use App\Models\Structure;
use Illuminate\Database\Eloquent\Collection;

final readonly class KraftPageDTO
{
    /**
     * @param  Collection<int, mixed>  $recipes
     * @param  array<int, array{id:int, count:int}>  $resources
     */
    public function __construct(
        public Structure $blacksmith,
        public Collection $recipes,
        public array $resources,
        public string $itemTooltipScript,
    ) {}
}
