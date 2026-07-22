<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Contracts;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface ItemReadRepository
{
    public function findItem(int $id): ?Item;

    public function findChestWithItems(int $id): ?Item;

    /**
     * @return Collection<int, ItemOnLocation>
     */
    public function getLocationItems(User $user): Collection;

    /**
     * @return Collection<int, User>
     */
    public function getOnlineUsersOnLocation(User $user): Collection;

    public function findUser(int $id): ?User;
}
