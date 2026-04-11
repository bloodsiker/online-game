<?php

declare(strict_types=1);

namespace App\Services\Dungeon;

use App\Enums\DungeonRunStatus;
use App\Models\Dungeon\Dungeon;
use App\Models\Dungeon\DungeonFloorBranch;
use App\Models\Dungeon\DungeonRun;
use App\Models\Dungeon\DungeonRunFloor;
use App\Repositories\DungeonRunRepository;

class SurvivalDungeonStrategy implements DungeonStrategyInterface
{
    public function __construct(
        private readonly DungeonRunRepository $runRepository,
    ) {}

    public function onRunStart(DungeonRun $run): void
    {
        // Track how many waves survived in metadata
        $run->update(['metadata' => array_merge($run->metadata ?? [], ['waves_survived' => 0])]);
    }

    public function onFloorComplete(DungeonRun $run, DungeonRunFloor $completedFloor): ?int
    {
        $meta    = $run->metadata ?? [];
        $survived = ($meta['waves_survived'] ?? 0) + 1;
        $run->update(['metadata' => array_merge($meta, ['waves_survived' => $survived])]);

        // Continue to next floor if exists
        $branch = DungeonFloorBranch::where('from_floor_id', $completedFloor->floor_id)->first();

        return $branch?->to_floor_id;
    }

    public function onFloorFail(DungeonRun $run, DungeonRunFloor $failedFloor): void
    {
        // Survival: complete the run with whatever waves survived (partial reward)
        $run->update(['status' => DungeonRunStatus::COMPLETED]);
    }

    public function isRunComplete(DungeonRun $run): bool
    {
        $currentFloor = $this->runRepository->getActiveRunFloor($run->id);
        if ($currentFloor === null) {
            return true;
        }

        return ! DungeonFloorBranch::where('from_floor_id', $currentFloor->floor_id)->exists();
    }

    public function getMonstersPerFloor(Dungeon $dungeon): int
    {
        // Survival scales monsters with current wave
        return $dungeon->monsters_per_floor ?? 5;
    }
}