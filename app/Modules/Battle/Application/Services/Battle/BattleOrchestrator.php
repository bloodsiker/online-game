<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;

readonly class BattleOrchestrator
{
    public function __construct(private BattleService $battleService) {}

    public function handleLocationEntry(Location $location): ?Battle
    {
        return $this->battleService->battleOnLocation($location);
    }

    public function handlePlayerAttack(Location $location, int $monsterId): ?Battle
    {
        return $this->battleService->attackMonster($location, $monsterId);
    }
}
