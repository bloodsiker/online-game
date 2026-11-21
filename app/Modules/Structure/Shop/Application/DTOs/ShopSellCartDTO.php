<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopSellCartDTO
{
    /**
     * @param  list<ShopSellCartLineDTO>  $lines
     */
    public function __construct(
        public array $lines = [],
    ) {}

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    public function totalPrice(): int
    {
        return array_sum(array_map(static fn (ShopSellCartLineDTO $line): int => $line->total(), $this->lines));
    }

    /** Сколько штук предмета уже лежит в корзине на продажу. */
    public function quantityOf(int $itemId): int
    {
        foreach ($this->lines as $line) {
            if ($line->itemId === $itemId) {
                return $line->quantity;
            }
        }

        return 0;
    }
}
