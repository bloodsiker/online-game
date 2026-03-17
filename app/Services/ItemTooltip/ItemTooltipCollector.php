<?php

namespace App\Services\ItemTooltip;

use App\Services\ItemTooltip\Strategy\ItemTooltipStrategyInterface;

class ItemTooltipCollector
{
    /** @var array<int, ItemTooltipDto> */
    private array $items = [];

    public function add(ItemTooltipDto $dto): void
    {
        if (isset($this->items[$dto->id])) {
            return;
        }

        $this->items[$dto->id] = $dto;
    }

    public function collectFrom(ItemTooltipStrategyInterface $strategy): static
    {
        $strategy->collect($this);

        return $this;
    }

    public function all(): array
    {
        return array_map(
            fn(ItemTooltipDto $dto) => $dto->toArray(),
            $this->items
        );
    }
}