<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopSellCartLineDTO
{
    public function __construct(
        public int $cartId,
        public int $itemId,
        public string $name,
        public string $color,
        public int $quantity,
        public int $unitPrice,
        public string $infoUrl,
    ) {}

    public function total(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}
