<?php

declare(strict_types=1);

namespace App\Modules\Location\Infrastructure\Persistence;

use App\Models\Dungeon\DungeonSession;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class EloquentLocationReadRepository implements LocationReadRepository
{
    public function findLocationOrFail(int $locationId): Location
    {
        return Location::with(['npcs', 'structures.actions'])->findOrFail($locationId);
    }

    public function getMonstersOnLocation(int $locationId): Collection
    {
        return MonsterOnLocation::with('monster')
            ->where('location_id', $locationId)
            ->where('active', 1)
            ->get();
    }

    public function getLocationUsers(int $locationId): Collection
    {
        return User::with(['player', 'clanMembership.clan'])
            ->where('location_id', $locationId)
            ->orderByDesc('last_online_at')
            ->get();
    }

    public function findDungeonSessionByUserId(int $userId): ?DungeonSession
    {
        return DungeonSession::with('dungeon')->where('user_id', $userId)->first();
    }

    public function getItemsOnLocation(User $user, int $locationId): Collection
    {
        $query = ItemOnLocation::with(['item', 'item.itemInfo'])->where('location_id', $locationId);

        $dungeonSessionId = null;
        if ($user->currentLocation->dungeon_id !== null) {
            $session = DungeonSession::where('user_id', $user->id)->first();
            $dungeonSessionId = $session?->monsterSessionId();
        }

        if ($dungeonSessionId !== null) {
            $query->where('dungeon_session_id', $dungeonSessionId);
        } else {
            $query->whereNull('dungeon_session_id');
        }

        return $query->get();
    }
}
