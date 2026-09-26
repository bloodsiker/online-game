<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Services;

use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventFavorite;

final class WorldEventFavoriteService
{
    public function set(int $userId, int $eventId, bool $favorite): void
    {
        if ($favorite) {
            WorldEventFavorite::query()->insertOrIgnore([
                'user_id' => $userId,
                'world_event_id' => $eventId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        WorldEventFavorite::query()
            ->where('user_id', $userId)
            ->where('world_event_id', $eventId)
            ->delete();
    }
}
