<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Domain\Contracts;

use App\Models\Share\ShareItem;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface ExchangeInventoryRepository
{
    /**
     * @return Collection<int, Backpack>
     */
    public function getBackpackItems(User $user): Collection;

    public function findShareItem(int $shareItemId): ?ShareItem;

    public function findBackpackItem(User $user, int $shareItemId): ?Backpack;

    public function saveBackpackItem(Backpack $backpack): void;

    public function deleteBackpackItem(Backpack $backpack): void;

    public function deleteItemById(int $itemId): void;

    public function createBackpackItem(User $user, int $shareItemId, int $count): void;
}
