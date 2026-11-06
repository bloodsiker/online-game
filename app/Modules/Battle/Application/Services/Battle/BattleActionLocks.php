<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BattleActionLocks
{
    /** Discover before opening the transaction so a waiting locking read cannot leave an old RR snapshot. */
    public function userIds(int $battleId, int $actorId): array
    {
        return BattleDetail::where('battle_id', $battleId)->whereNotNull('user_id')->pluck('user_id')
            ->merge(DB::table('battle_monster_damage')->where('battle_id', $battleId)->pluck('user_id'))
            ->push($actorId)->filter()->unique()->sort()->values()->all();
    }

    /** Lock potential loot recipients too, in the same order for every participant. */
    public function users(array $ids): Collection
    {
        return collect($ids)->unique()->sort()->mapWithKeys(
            fn ($id): array => [$id => User::without('player')->whereKey($id)->lockForUpdate()->firstOrFail()],
        );
    }
}
