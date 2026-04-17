<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Dungeon\Dungeon;
use App\Models\Dungeon\DungeonFloor;
use Illuminate\Support\Collection;

class DungeonRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Dungeon::class;
    }

    public function findWithFloors(int $dungeonId): ?Dungeon
    {
        return Dungeon::with(['floors.nextBranches', 'floors.monsterPool', 'floors.bosses'])
            ->find($dungeonId);
    }

    public function getAllActive(): Collection
    {
        return Dungeon::where('is_active', true)
            ->orderBy('tier')
            ->get();
    }

    public function getFirstFloor(int $dungeonId): ?DungeonFloor
    {
        return DungeonFloor::where('dungeon_id', $dungeonId)
            ->where('is_first', true)
            ->first();
    }

    public function getFloorById(int $floorId): ?DungeonFloor
    {
        return DungeonFloor::with(['nextBranches', 'monsterPool', 'bosses'])
            ->find($floorId);
    }

    public function getNextFloors(int $floorId): Collection
    {
        return DungeonFloor::whereHas('prevBranches', fn ($q) => $q->where('from_floor_id', $floorId))
            ->get();
    }
}
