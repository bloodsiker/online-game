<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\Services;

use App\Modules\Structure\Prison\Infrastructure\Persistence\Models\PlayerDetention;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Item\Infrastructure\Persistence\Models\LockpickingAttempt;
use App\Modules\Location\Infrastructure\Persistence\Models\GatheringAttempt;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AdminPlayerTeleportService
{
    public function teleport(User $user, Location $location): void
    {
        $oldLocationId = (int) $user->location_id;

        DB::transaction(function () use ($user, $location): void {
            $lockedUser = User::query()
                ->without('player')
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveDetention = PlayerDetention::query()
                ->where('user_id', $lockedUser->id)
                ->whereNull('released_at')
                ->where('release_at', '>', now())
                ->exists();
            if ($hasActiveDetention) {
                throw ValidationException::withMessages([
                    'player' => 'Нельзя телепортировать игрока, пока он отбывает наказание в Темнице.',
                ]);
            }

            GatheringAttempt::query()->where('player_id', $lockedUser->player_id)->delete();
            LockpickingAttempt::query()->where('player_id', $lockedUser->player_id)->delete();

            $lockedUser->prev_location_id = $lockedUser->location_id;
            $lockedUser->location_id = $location->id;
            $lockedUser->save();
        });

        Cache::forget('who:users_on_location:'.$oldLocationId);
        Cache::forget('who:users_on_location:'.(int) $location->id);

        $dungeonRunId = DungeonSession::query()->where('user_id', $user->id)->value('dungeon_run_id');
        if ($dungeonRunId !== null) {
            Cache::forget('who:users_on_location:'.$oldLocationId.':run:'.$dungeonRunId);
            Cache::forget('who:users_on_location:'.(int) $location->id.':run:'.$dungeonRunId);
        }
    }
}
