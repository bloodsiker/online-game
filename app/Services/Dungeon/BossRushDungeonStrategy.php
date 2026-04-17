<?php

declare(strict_types=1);

namespace App\Services\Dungeon;

use App\Enums\DungeonRunStatus;
use App\Models\Dungeon\Dungeon;
use App\Models\Dungeon\DungeonBoss;
use App\Models\Dungeon\DungeonFloorBranch;
use App\Models\Dungeon\DungeonRun;
use App\Models\Dungeon\DungeonRunFloor;
use App\Repositories\DungeonRunRepository;

class BossRushDungeonStrategy implements DungeonStrategyInterface
{
    public function __construct(
        private readonly DungeonRunRepository $runRepository,
    ) {}

    public function onRunStart(DungeonRun $run): void
    {
        $bossCount = DungeonBoss::where('dungeon_id', $run->dungeon_id)->count();
        $run->update(['metadata' => array_merge($run->metadata ?? [], [
            'bosses_total' => $bossCount,
            'bosses_defeated' => 0,
        ])]);
    }

    public function onFloorComplete(DungeonRun $run, DungeonRunFloor $completedFloor): ?int
    {
        $meta = $run->metadata ?? [];
        $defeated = ($meta['bosses_defeated'] ?? 0) + 1;
        $run->update(['metadata' => array_merge($meta, ['bosses_defeated' => $defeated])]);

        $branch = DungeonFloorBranch::where('from_floor_id', $completedFloor->floor_id)->first();

        return $branch?->to_floor_id;
    }

    public function onFloorFail(DungeonRun $run, DungeonRunFloor $failedFloor): void
    {
        // Boss rush: fail entire run, no partial rewards
        $run->update(['status' => DungeonRunStatus::FAILED]);
    }

    public function isRunComplete(DungeonRun $run): bool
    {
        $meta = $run->metadata ?? [];
        $total = $meta['bosses_total'] ?? 0;
        $defeated = $meta['bosses_defeated'] ?? 0;

        return $total > 0 && $defeated >= $total;
    }

    public function getMonstersPerFloor(Dungeon $dungeon): int
    {
        // Boss rush: only 1 boss per floor
        return 1;
    }
}
