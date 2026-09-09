<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Contracts;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface BlacksmithInventoryRepository
{
    public function findRecipeSlot(User $user, int $recipeItemId): ?Backpack;

    public function findOwnedSlot(User $user, int $itemId, array $with = []): ?Backpack;

    public function findOwnedSlotByTypes(User $user, int $itemId, array $types, array $with = []): ?Backpack;

    public function findOwnedSlotByShareItemId(User $user, int $shareItemId): ?Backpack;

    /** @param list<int> $itemIds
     * @return Collection<int, Backpack>
     */
    public function findOwnedSlotsForUpdate(User $user, array $itemIds): Collection;

    public function findOwnedSlotByShareItemIdForUpdate(User $user, int $shareItemId): ?Backpack;
}
