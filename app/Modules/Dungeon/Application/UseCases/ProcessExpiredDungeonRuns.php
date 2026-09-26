<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Dungeon\Domain\Enums\DungeonRunStatus;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonRun;
use Carbon\CarbonInterface;

final readonly class ProcessExpiredDungeonRuns
{
    public function __construct(private DungeonCoordinator $coordinator) {}

    public function execute(CarbonInterface $now): int
    {
        $processed = 0;

        DungeonRun::query()
            ->with(['sessions.user'])
            ->where('status', DungeonRunStatus::ACTIVE->value)
            ->where(function ($query) use ($now): void {
                $query->where('stage_expires_at', '<=', $now)
                    ->orWhere('expires_at', '<=', $now);
            })
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(function (DungeonRun $run) use (&$processed): void {
                $session = $run->sessions->first();
                if ($session?->user !== null && $this->coordinator->expireSessionIfNeeded($session->user)) {
                    $processed++;
                }
            });

        return $processed;
    }
}
