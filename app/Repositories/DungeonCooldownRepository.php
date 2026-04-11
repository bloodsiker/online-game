<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Dungeon\DungeonCooldown;
use Carbon\Carbon;

class DungeonCooldownRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return DungeonCooldown::class;
    }

    public function findPersonal(int $dungeonId, int $userId): ?DungeonCooldown
    {
        return DungeonCooldown::where('dungeon_id', $dungeonId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findGlobal(int $dungeonId): ?DungeonCooldown
    {
        return DungeonCooldown::where('dungeon_id', $dungeonId)
            ->where('user_id', 0)
            ->first();
    }

    public function isPersonalOnCooldown(int $dungeonId, int $userId): bool
    {
        return DungeonCooldown::where('dungeon_id', $dungeonId)
            ->where('user_id', $userId)
            ->where('available_at', '>', now())
            ->exists();
    }

    public function isGlobalOnCooldown(int $dungeonId): bool
    {
        return DungeonCooldown::where('dungeon_id', $dungeonId)
            ->where('user_id', 0)
            ->where('available_at', '>', now())
            ->exists();
    }

    public function setPersonal(int $dungeonId, int $userId, Carbon $availableAt): void
    {
        DungeonCooldown::updateOrCreate(
            ['dungeon_id' => $dungeonId, 'user_id' => $userId],
            ['available_at' => $availableAt],
        );
    }

    public function setGlobal(int $dungeonId, Carbon $availableAt): void
    {
        DungeonCooldown::updateOrCreate(
            ['dungeon_id' => $dungeonId, 'user_id' => 0],
            ['available_at' => $availableAt],
        );
    }
}