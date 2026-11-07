<?php

declare(strict_types=1);

namespace App\Modules\Interface\Infrastructure\Persistence;

use App\Modules\Interface\Domain\Contracts\OnlinePlayersReader;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class EloquentOnlinePlayersReader implements OnlinePlayersReader
{
    public function count(): int
    {
        return (int) Cache::remember('mini-app:online-count:'.(int) floor(time() / 10), 10,
            fn (): int => $this->query()->count());
    }

    public function page(int $page, int $perPage): LengthAwarePaginator
    {
        return $this->query()->select(['id', 'name', 'player_id'])->with([
            'player' => static fn (BelongsTo $relation): BelongsTo => $relation->withOnly([])->select(['id', 'lvl']),
            'clanMembership.clan' => static fn (BelongsTo $relation): BelongsTo => $relation->withOnly([])->select(['id', 'name', 'icon']),
        ])->orderBy('name')->orderBy('id')->paginate(perPage: $perPage, page: $page)
            ->through(static function (User $user): array {
                $clan = $user->clanMembership?->clan;

                return ['id' => (int) $user->id, 'name' => $user->name, 'level' => (int) $user->player?->lvl,
                    'clan_name' => $clan?->name, 'clan_icon' => $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
                    'profile_url' => route('info.user', ['id' => $user->id])];
            });
    }

    private function query(): Builder
    {
        return User::query()->without('player')->whereNotNull('email_verified_at')
            ->whereHas('player')->where('last_online_at', '>=', now()->subMinutes((int) config('game.online_timeout_minutes', 30)));
    }
}
