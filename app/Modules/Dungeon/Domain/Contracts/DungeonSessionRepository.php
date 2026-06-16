<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Contracts;

use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use Carbon\CarbonInterface;

interface DungeonSessionRepository
{
    public function findByUserId(int $userId): ?DungeonSession;

    public function existsForUser(int $userId): bool;

    public function create(Dungeon $dungeon, int $userId, ?CarbonInterface $expiresAt = null, ?int $primarySessionId = null): DungeonSession;

    public function delete(DungeonSession $session): void;

    public function hasFollowers(int $primarySessionId): bool;

    public function incrementWave(DungeonSession $session): DungeonSession;

    public function markCompleted(DungeonSession $session): void;
}
