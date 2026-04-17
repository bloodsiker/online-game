<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\DungeonParticipantStatus;
use App\Enums\DungeonRunFloorStatus;
use App\Enums\DungeonRunStatus;
use App\Models\Dungeon\DungeonRun;
use App\Models\Dungeon\DungeonRunFloor;
use App\Models\Dungeon\DungeonRunLog;
use App\Models\Dungeon\DungeonRunParticipant;
use Illuminate\Support\Collection;

class DungeonRunRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return DungeonRun::class;
    }

    public function findActiveByUser(int $userId): ?DungeonRun
    {
        return DungeonRun::where('status', DungeonRunStatus::ACTIVE)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId)
                ->where('status', DungeonParticipantStatus::ACTIVE))
            ->with(['dungeon', 'currentFloor.floor', 'participants.user'])
            ->first();
    }

    public function findActiveById(int $runId): ?DungeonRun
    {
        return DungeonRun::where('id', $runId)
            ->where('status', DungeonRunStatus::ACTIVE)
            ->with(['dungeon', 'currentFloor.floor', 'participants'])
            ->first();
    }

    public function createRun(array $data): DungeonRun
    {
        return DungeonRun::create($data);
    }

    public function addParticipant(int $runId, int $userId): DungeonRunParticipant
    {
        return DungeonRunParticipant::create([
            'run_id' => $runId,
            'user_id' => $userId,
            'status' => DungeonParticipantStatus::ACTIVE,
        ]);
    }

    public function createRunFloor(array $data): DungeonRunFloor
    {
        return DungeonRunFloor::create($data);
    }

    public function getActiveRunFloor(int $runId): ?DungeonRunFloor
    {
        return DungeonRunFloor::where('run_id', $runId)
            ->where('status', DungeonRunFloorStatus::ACTIVE)
            ->with(['floor.nextBranches', 'battle'])
            ->first();
    }

    public function getRunFloors(int $runId): Collection
    {
        return DungeonRunFloor::where('run_id', $runId)
            ->with('floor')
            ->orderBy('id')
            ->get();
    }

    public function log(int $runId, ?int $userId, string $event, ?array $payload = null): void
    {
        DungeonRunLog::create([
            'run_id' => $runId,
            'user_id' => $userId,
            'event' => $event,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    public function markParticipantDead(int $runId, int $userId): void
    {
        DungeonRunParticipant::where('run_id', $runId)
            ->where('user_id', $userId)
            ->update(['status' => DungeonParticipantStatus::DEAD]);
    }

    public function getActiveParticipants(int $runId): Collection
    {
        return DungeonRunParticipant::where('run_id', $runId)
            ->where('status', DungeonParticipantStatus::ACTIVE)
            ->with('user.player')
            ->get();
    }
}
