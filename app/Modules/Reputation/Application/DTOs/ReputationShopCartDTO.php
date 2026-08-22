<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\DTOs;

use Illuminate\Database\Eloquent\Collection;

final class ReputationShopCartDTO
{
    public function __construct(
        protected readonly Collection $items,
        protected readonly int $totalDiamond,
        protected readonly int $totalPrice,
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
}
