<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\DTO;

use Illuminate\Database\Eloquent\Collection;

final class BackpackDTO
{
    public function __construct(
        protected readonly Collection $backpack,
        protected readonly Collection $items,
        protected readonly int $countItems,
        protected readonly string $group,
    ) {}

    public function getBackpack(): Collection
    {
        return $this->backpack;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getCountItems(): int
    {
        return $this->countItems;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function hasWeapon(): bool
    {
        return $this->items->has( 'weapon');
    }

    public function getWeapon(): Collection
    {
        return $this->items->get( 'weapon');
    }

    public function hasBag(): bool
    {
        return $this->items->has( 'bag');
    }

    public function getBag(): Collection
    {
        return $this->items->get( 'bag');
    }

    public function hasBelt(): bool
    {
        return $this->items->has( 'belt');
    }

    public function getBelt(): Collection
    {
        return $this->items->get( 'belt');
    }

    public function hasShield(): bool
    {
        return $this->items->has( 'shield');
    }

    public function getShield(): Collection
    {
        return $this->items->get( 'shield');
    }

    public function hasArmor(): bool
    {
        return $this->items->has( 'armor');
    }

    public function getArmor(): Collection
    {
        return $this->items->get( 'armor');
    }

    public function hasPotion(): bool
    {
        return $this->items->has( 'potion');
    }

    public function getPotion(): Collection
    {
        return $this->items->get( 'potion');
    }

    public function hasResource(): bool
    {
        return $this->items->has( 'resource');
    }

    public function getResource(): Collection
    {
        return $this->items->get( 'resource');
    }

    public function hasScroll(): bool
    {
        return $this->items->has( 'scroll');
    }

    public function getScroll(): Collection
    {
        return $this->items->get( 'scroll');
    }

    public function hasRecipe(): bool
    {
        return $this->items->has( 'recipe');
    }

    public function getRecipe(): Collection
    {
        return $this->items->get( 'recipe');
    }
}