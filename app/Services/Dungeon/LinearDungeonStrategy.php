<?php

declare(strict_types=1);

namespace App\Services\Dungeon;

use App\Models\Dungeon\Dungeon;
use App\Models\Dungeon\DungeonFloorBranch;
use App\Models\Dungeon\DungeonRun;
use App\Models\Dungeon\DungeonRunFloor;
use App\Repositories\DungeonRepository;
use App\Repositories\DungeonRunRepository;

class LinearDungeonStrategy implements DungeonStrategyInterface
{
    public function __construct(
        private readonly DungeonRepository $dungeonRepository,
        private readonly DungeonRunRepository $runRepository,
    ) {}

    public function onRunStart(DungeonRun $run): void
    {
        // No special metadata needed for linear dungeons
    }

    public function onFloorComplete(DungeonRun $run, DungeonRunFloor $completedFloor): ?int
    {
        // Follow single branch (linear = no branching, just next_floor_id)
        $branch = DungeonFloorBranch::where('from_floor_id', $completedFloor->floor_id)
            ->first();

        return $branch?->to_floor_id;
    }

    public function onFloorFail(DungeonRun $run, DungeonRunFloor $failedFloor): void
    {
        // Linear dungeon fails the whole run on floor failure
        $run->update(['status' => \App\Enums\DungeonRunStatus::FAILED]);
    }

    public function isRunComplete(DungeonRun $run): bool
    {
        // Complete when current floor has no next branch
        $currentFloor = $this->runRepository->getActiveRunFloor($run->id);
        if ($currentFloor === null) {
            return true;
        }

        return ! DungeonFloorBranch::where('from_floor_id', $currentFloor->floor_id)->exists();
    }

    public function getMonstersPerFloor(Dungeon $dungeon): int
    {
        return $dungeon->monsters_per_floor ?? 3;
    }
}
