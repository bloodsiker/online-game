<?php

namespace App\Services\Battle;

use App\Models\Location;
use App\Models\Battle\Battle;

class BattleOrchestrator
{
    public function __construct(
        private BattleFinder $finder,
        private MonsterSpawner $spawner,
        private BattleCreator $creator,
        private PlayerAttackInitiator $attackInitiator,
    ) {}

    public function handleLocationEntry(Location $location): ?Battle
    {
        $battle = $this->finder->findActiveForPlayer($location);
        if ($battle) {
            return $battle;
        }

        $aggressiveMonsters = $this->spawner->spawnAndGetAggressive($location);

        if ($aggressiveMonsters->isNotEmpty()) {
            return $this->creator->createWithMonsters($location, $aggressiveMonsters);
        }

        return null;
    }

    public function handlePlayerAttack(Location $location, int $monsterId): ?Battle
    {
        return $this->attackInitiator->attack($location, $monsterId);
    }
}
