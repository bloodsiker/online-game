<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\DTOs;

final readonly class ItemLocationEntryDTO
{
    public function __construct(
        public string $image,
        public string $name,
        public int $count,
        public string $actionLabel,
        public string $actionUrl,
    ) {}
}
