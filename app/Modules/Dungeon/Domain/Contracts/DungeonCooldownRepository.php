<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Contracts;

use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonCooldown;
use Carbon\Carbon;

interface DungeonCooldownRepository
{
    public function findPersonal(int $dungeonId, int $userId): ?DungeonCooldown;

    public function findGlobal(int $dungeonId): ?DungeonCooldown;

    public function isPersonalOnCooldown(int $dungeonId, int $userId): bool;

    public function isGlobalOnCooldown(int $dungeonId): bool;

    public function setPersonal(int $dungeonId, int $userId, Carbon $availableAt): void;

    public function setGlobal(int $dungeonId, Carbon $availableAt): void;
}
