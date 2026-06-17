<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence;

use App\Modules\Dungeon\Domain\Contracts\DungeonSessionRepository;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use Carbon\CarbonInterface;

class EloquentDungeonSessionRepository implements DungeonSessionRepository
{
    public function findByUserId(int $userId): ?DungeonSession
    {
        return DungeonSession::query()
            ->with(['dungeon', 'user.currentLocation'])
            ->where('user_id', $userId)
            ->first();
    }

    public function existsForUser(int $userId): bool
    {
        return DungeonSession::query()
            ->where('user_id', $userId)
            ->exists();
    }

    public function create(Dungeon $dungeon, int $userId, ?CarbonInterface $expiresAt = null, ?int $primarySessionId = null): DungeonSession
    {
        return DungeonSession::query()->create([
            'dungeon_id' => $dungeon->id,
            'user_id' => $userId,
            'primary_session_id' => $primarySessionId,
            'expires_at' => $expiresAt,
        ]);
    }

    public function delete(DungeonSession $session): void
    {
        $session->delete();
    }

    public function hasFollowers(int $primarySessionId): bool
    {
        return DungeonSession::query()
            ->where('primary_session_id', $primarySessionId)
            ->exists();
    }

    public function incrementWave(DungeonSession $session): DungeonSession
    {
        $session->increment('current_wave');

        return $session->refresh();
    }

    public function markCompleted(DungeonSession $session): void
    {
        $session->completed_at = now();
        $session->save();
    }
}
