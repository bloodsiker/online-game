<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class ChestPageDTO
{
    /**
     * @param  list<ChestEntryDTO>  $items
     */
    public function __construct(
        public int $count,
        public array $items,
        public string $message,
        public string $backpackUrl,
        public string $locationUrl,
    ) {}
}
