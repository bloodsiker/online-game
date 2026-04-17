<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\DTOs;

use App\Models\Share\ShareItem;

final readonly class ExchangeItemDTO
{
    public function __construct(
        public int $id,
        public ShareItem $fromItem,
        public ShareItem $toItem,
        public int $fromAmount,
        public int $toAmount,
        public int $availableCount,
    ) {}
}
