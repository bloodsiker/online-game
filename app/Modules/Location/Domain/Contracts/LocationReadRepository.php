<?php

declare(strict_types=1);

namespace App\Modules\Location\Domain\Contracts;

use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface LocationReadRepository
{
    public function findLocationOrFail(int $locationId): Location;

    /**
     * @return Collection<int, MonsterOnLocation>
     */
    public function getMonstersOnLocation(int $locationId): Collection;

    /**
     * @return Collection<int, User>
     */
    public function getLocationUsers(int $locationId): Collection;

    public function findDungeonSessionByUserId(int $userId): ?DungeonSession;

    /**
     * @return Collection<int, ItemOnLocation>
     */
    public function getItemsOnLocation(User $user, int $locationId): Collection;
}
