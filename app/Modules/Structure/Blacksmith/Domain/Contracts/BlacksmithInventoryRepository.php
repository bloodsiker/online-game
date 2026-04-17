<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Contracts;

use App\Models\User;
use App\Modules\Backpack\Domain\Models\Backpack;

interface BlacksmithInventoryRepository
{
    public function findRecipeSlot(User $user, int $recipeItemId): ?Backpack;

    public function findOwnedSlot(User $user, int $itemId, array $with = []): ?Backpack;

    public function findOwnedSlotByTypes(User $user, int $itemId, array $types, array $with = []): ?Backpack;

    public function findOwnedSlotByShareItemId(User $user, int $shareItemId): ?Backpack;
}
