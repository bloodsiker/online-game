<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopBuyItemDTO
{
    /**
     * @param  list<array{id: int, name: string, image: string, color: string, quantity: int, availableQuantity: int, infoUrl: string}>  $requirements
     */
    public function __construct(
        public int $shopItemId,
        public int $itemId,
        public string $name,
        public string $color,
        public string $image,
        public string $typeName,
        public ?int $requiredLevel,
        public int $price,
        public int $diamond,
        public array $requirements,
        public string $infoUrl,
    ) {}
}
