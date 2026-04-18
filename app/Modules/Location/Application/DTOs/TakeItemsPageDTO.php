<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\DTOs;

final readonly class TakeItemsPageDTO
{
    /**
     * @param  list<TakeLocationItemDTO>  $items
     */
    public function __construct(
        public int $count,
        public array $items,
        public string $backUrl,
    ) {}
}
