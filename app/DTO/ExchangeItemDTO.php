<?php

namespace App\DTO;

use App\Models\Share\ShareItem;

final class ExchangeItemDTO {
    public function __construct(
        protected int $id,
        protected ShareItem $fromItem,
        protected ShareItem $toItem,
        protected int $fromAmount,
        protected int $toAmount,
        protected int $availableCount
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getFromItem(): ShareItem
    {
        return $this->fromItem;
    }

    public function getToItem(): ShareItem
    {
        return $this->toItem;
    }

    public function getFromAmount(): int
    {
        return $this->fromAmount;
    }

    public function getToAmount(): int
    {
        return $this->toAmount;
    }

    public function getAvailableCount(): int
    {
        return $this->availableCount;
    }
}
