<?php

declare(strict_types=1);

namespace App\Modules\Location\Infrastructure\Persistence;

use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationGate;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
        // Тот же список, что и в InterfaceReadRepository::getUsersOnLocation —
        // используем общий ключ кэша, чтобы не дублировать запросы.
        return Cache::remember(
            'who:users_on_location:'.$locationId,
            now()->addSeconds(60),
            fn (): Collection => User::with(['player', 'clanMembership.clan'])
                ->where('location_id', $locationId)
                ->orderByDesc('last_online_at')
                ->get(),
        );
    }

    public function findDungeonSessionByUserId(int $userId): ?DungeonSession
    {
        return DungeonSession::with('dungeon')->where('user_id', $userId)->first();
    }

    public function findTeleportUseGate(int $shareItemId, int $fromLocationId): ?LocationGate
    {
        return LocationGate::query()
            ->where('share_item_id', $shareItemId)
            ->where('mode', 'teleport_use')
            ->where('from_location_id', $fromLocationId)
            ->first();
    }

    public function getTeleportUseShareItemIds(int $fromLocationId): array
    {
        return LocationGate::query()
            ->where('mode', 'teleport_use')
            ->where('from_location_id', $fromLocationId)
            ->distinct()
            ->pluck('share_item_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    public function getItemsOnLocation(User $user, int $locationId): Collection
    {
        return $this->visibleItemsOnLocationQuery($user, $locationId)
            ->with(['item', 'item.itemInfo'])
            ->get();
    }

    public function countItemsOnLocation(User $user, int $locationId): int
    {
        return $this->visibleItemsOnLocationQuery($user, $locationId)->count();
    }

    private function visibleItemsOnLocationQuery(User $user, int $locationId): Builder
    {
        $query = ItemOnLocation::visible()
            ->where('location_id', $locationId);

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

        return $query;
    }
}
