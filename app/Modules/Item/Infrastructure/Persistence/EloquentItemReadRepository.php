<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence;

use App\Models\Dungeon\DungeonSession;
use App\Modules\Item\Domain\Contracts\ItemReadRepository;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class EloquentItemReadRepository implements ItemReadRepository
{
    public function findItem(int $id): ?Item
    {
        return Item::find($id);
    }

    public function findChestWithItems(int $id): ?Item
    {
        return Item::with('itemsInChest')->find($id);
    }

    public function getLocationItems(User $user): Collection
    {
        $query = ItemOnLocation::with(['item', 'item.itemInfo'])
            ->where('location_id', $user->location_id);

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

    public function getOnlineUsersOnLocation(User $user): Collection
    {
        return User::with(['player'])
            ->where('location_id', $user->location_id)
            ->whereNot('id', $user->id)
            ->where('last_online_at', '>', now()->subMinutes(10))
            ->orderByDesc('last_online_at')
            ->get();
    }

    public function findUser(int $id): ?User
    {
        return User::find($id);
    }
}
