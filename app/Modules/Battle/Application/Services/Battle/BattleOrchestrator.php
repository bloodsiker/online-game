<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Dungeon\Application\UseCases\AdvanceSurvivalWave;
use App\Modules\Dungeon\Application\UseCases\GetActiveDungeonSession;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Support\Facades\Auth;

readonly class BattleOrchestrator
{
    public function __construct(
        private BattleFinder $finder,
        private MonsterSpawner $spawner,
        private BattleCreator $creator,
        private PlayerAttackInitiator $attackInitiator,
        private GetActiveDungeonSession $getActiveDungeonSession,
        private AdvanceSurvivalWave $advanceSurvivalWave,
    ) {}

    public function handleLocationEntry(Location $location): ?Battle
    {
        $dungeonSessionId = null;

        if ($location->dungeon_id !== null) {
            $user = Auth::user();
            $session = $this->getActiveDungeonSession->execute($user->id);
            if ($session === null) {
                return null;
            }
            $this->advanceSurvivalWave->execute($session, $location);
            $dungeonSessionId = $session->monsterSessionId();
        }

        $battle = $this->finder->findActiveForPlayer($location);
        if ($battle) {
            return $battle;
        }

        $aggressiveMonsters = $this->spawner->spawnAndGetAggressive($location, $dungeonSessionId);

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
