<?php

namespace App\Modules\Structure\Shop\Application\DTOs;

use Illuminate\Database\Eloquent\Collection;

final class ShopCartDTO
{
    public function __construct(
        protected readonly Collection $items,
        protected readonly int $totalDiamond,
        protected readonly int $totalPrice,
        protected readonly array $requirementTotals = [],
    ) {}

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getTotalDiamond(): int
    {
        return $this->totalDiamond;
    }

    public function getTotalPrice(): int
    {
        return $this->totalPrice;
    }

    /**
     * @return list<array{item: object, quantity: int}>
     */
    public function getRequirementTotals(): array
    {
        return $this->requirementTotals;
    }
}
