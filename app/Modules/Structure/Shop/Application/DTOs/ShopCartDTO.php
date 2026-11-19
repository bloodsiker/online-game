<?php

namespace App\Modules\Structure\Shop\Application\DTOs;

use App\Modules\Structure\Shop\Application\Services\CityShopDiscountService;
use Illuminate\Database\Eloquent\Collection;

final class ShopCartDTO
{
    public function __construct(
        protected readonly Collection $items,
        protected readonly int $totalDiamond,
        protected readonly int $totalPrice,
        protected readonly array $requirementTotals = [],
        protected readonly int $discountPercent = 0,
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

    /** Скидка «Городской стражи» в этом магазине, %. */
    public function getDiscountPercent(): int
    {
        return $this->discountPercent;
    }

    /** Цена в монетах за штуку с учётом скидки. */
    public function unitPrice(int $basePrice): int
    {
        return CityShopDiscountService::apply($basePrice, $this->discountPercent);
    }

    /**
     * @return list<array{item: object, quantity: int}>
     */
    public function getRequirementTotals(): array
    {
        return $this->requirementTotals;
    }
}
