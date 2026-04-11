<?php

declare(strict_types=1);

namespace App\Services\Dungeon;

use App\Models\Dungeon\Dungeon;
use App\Models\Dungeon\DungeonRun;
use App\Models\Dungeon\DungeonRunFloor;

interface DungeonStrategyInterface
{
    /**
     * Called when a new run is created — may set initial metadata.
     */
    public function onRunStart(DungeonRun $run): void;

    /**
     * Called when a floor battle is won.
     * Returns the next floor to enter, or null if the dungeon is complete.
     */
    public function onFloorComplete(DungeonRun $run, DungeonRunFloor $completedFloor): ?int;

    /**
     * Called when a floor battle is lost (all participants dead or timer expired).
     */
    public function onFloorFail(DungeonRun $run, DungeonRunFloor $failedFloor): void;

    /**
     * Whether this run is considered fully completed (all required floors done).
     */
    public function isRunComplete(DungeonRun $run): bool;

    /**
     * Number of monsters to spawn on a floor (may differ per strategy).
     */
    public function getMonstersPerFloor(Dungeon $dungeon): int;
}