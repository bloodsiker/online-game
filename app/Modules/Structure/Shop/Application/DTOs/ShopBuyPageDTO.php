<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopBuyPageDTO
{
    /**
     * @param  list<ShopBuyItemDTO>  $items
     */
    public function __construct(
        public int $shopId,
        public int $money,
        public int $diamonds,
        public array $items,
    ) {}
}
