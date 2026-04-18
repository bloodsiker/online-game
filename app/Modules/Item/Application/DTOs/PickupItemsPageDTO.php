<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class PickupItemsPageDTO
{
    /**
     * @param  list<ItemLocationEntryDTO>  $items
     */
    public function __construct(
        public int $count,
        public array $items,
        public string $message,
        public string $locationUrl,
        public string $backpackUrl,
    ) {}
}
