<?php

declare(strict_types=1);

namespace App\Modules\Interface\Infrastructure\Persistence;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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
        return Cache::remember(
            'who:users_on_location:'.$locationId,
            now()->addSeconds(60),
            fn (): Collection => User::with(['player', 'clanMembership.clan'])
                ->where('location_id', $locationId)
                ->orderByDesc('last_online_at')
                ->get(),
        );
    }

    public function getOnlineUsers(Carbon $threshold): Collection
    {
        $bucket = (int) floor(time() / 60);

        return Cache::remember(
            'who:online_users:'.$bucket,
            now()->addSeconds(75),
            fn (): Collection => User::with(['player', 'clanMembership.clan'])
                ->where('last_online_at', '>=', $threshold)
                ->orderByDesc('last_online_at')
                ->get(),
        );
    }

    public function getPlayerActiveEffects(int $playerId): Collection
    {
        $damageEffectTypes = collect(ActiveEffectType::cases())
            ->filter(static fn (ActiveEffectType $type): bool => $type->isDoT())
            ->map(static fn (ActiveEffectType $type): string => $type->value)
            ->all();

        return PlayerActiveEffect::where('player_id', $playerId)
            ->where(function ($query) use ($damageEffectTypes): void {
                $query->where(function ($timedQuery): void {
                    $timedQuery->whereNull('battle_id')
                        ->where(fn ($expiresQuery) => $expiresQuery
                            ->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now()));
                })->orWhere(function ($battleQuery) use ($damageEffectTypes): void {
                    $battleQuery->whereNotNull('battle_id')
                        ->whereIn('type', $damageEffectTypes)
                        ->where('stacks', '>', 0)
                        ->whereHas('battle', fn ($query) => $query->where('status', BattleStatus::ACTIVE));
                });
            })
            ->with('effect')
            ->get();
    }
}
