<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\DTOs;

use App\Models\Structure;
use Illuminate\Support\Collection;

final readonly class ExchangePageDTO
{
    /**
     * @param  Collection<int, ExchangeItemDTO>  $items
     */
    public function __construct(
        public Structure $exchange,
        public Collection $items,
    ) {}
}
