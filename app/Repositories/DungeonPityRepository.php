<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Dungeon\DungeonPity;

class DungeonPityRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return DungeonPity::class;
    }

    public function findOrCreate(int $dungeonId, int $userId): DungeonPity
    {
        return DungeonPity::firstOrCreate(
            ['dungeon_id' => $dungeonId, 'user_id' => $userId],
            ['fail_count' => 0],
        );
    }

    public function incrementFail(int $dungeonId, int $userId): void
    {
        DungeonPity::updateOrCreate(
            ['dungeon_id' => $dungeonId, 'user_id' => $userId],
            [],
        );

        DungeonPity::where('dungeon_id', $dungeonId)
            ->where('user_id', $userId)
            ->increment('fail_count', 1, ['last_fail_at' => now()]);
    }

    public function resetFail(int $dungeonId, int $userId): void
    {
        DungeonPity::where('dungeon_id', $dungeonId)
            ->where('user_id', $userId)
            ->update([
                'fail_count'   => 0,
                'last_pity_at' => now(),
            ]);
    }

    public function getFailCount(int $dungeonId, int $userId): int
    {
        return DungeonPity::where('dungeon_id', $dungeonId)
            ->where('user_id', $userId)
            ->value('fail_count') ?? 0;
    }
}