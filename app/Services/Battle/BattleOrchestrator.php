<?php

namespace App\Services\Battle;

use App\Models\Battle\Battle;
use App\Models\Dungeon\DungeonSession;
use App\Models\Location\Location;
use App\Services\DungeonService;
use Illuminate\Support\Facades\Auth;

readonly class BattleOrchestrator
{
    public function __construct(
        private BattleFinder $finder,
        private MonsterSpawner $spawner,
        private BattleCreator $creator,
        private PlayerAttackInitiator $attackInitiator,
        private DungeonService $dungeonService,
    ) {}

    public function handleLocationEntry(Location $location): ?Battle
    {
        $dungeonSessionId = null;

        if ($location->dungeon_id !== null) {
            $user = Auth::user();
            $session = DungeonSession::where('user_id', $user->id)->first();
            if ($session === null) {
                return null;
            }
            $this->dungeonService->tryAdvanceSurvivalWave($session, $location);
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
