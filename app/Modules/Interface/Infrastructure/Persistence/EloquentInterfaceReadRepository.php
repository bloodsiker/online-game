<?php

declare(strict_types=1);

namespace App\Modules\Interface\Infrastructure\Persistence;

use App\Models\Map;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class EloquentInterfaceReadRepository implements InterfaceReadRepository
{
    public function findMapBySlug(string $slug): ?Map
    {
        return Map::where('slug', $slug)->first();
    }

    public function viewExists(string $view): bool
    {
        return View::exists($view);
    }

    public function getUsersOnLocation(int $locationId): Collection
    {
        return User::with(['player', 'clanMembership.clan'])
            ->where('location_id', $locationId)
            ->orderByDesc('last_online_at')
            ->get();
    }

    public function getOnlineUsers(Carbon $threshold): Collection
    {
        return User::with(['player', 'clanMembership.clan'])
            ->where('last_online_at', '>=', $threshold)
            ->orderByDesc('last_online_at')
            ->get();
    }

    public function getPlayerActiveEffects(int $playerId): Collection
    {
        return PlayerActiveEffect::where('player_id', $playerId)
            ->whereNull('battle_id')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('effect')
            ->get();
    }
}
