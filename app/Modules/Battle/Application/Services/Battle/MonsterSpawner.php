<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Repositories\MonsterOnLocationRepository;
use Illuminate\Support\Collection;

readonly class MonsterSpawner
{
    public function __construct(
        private MonsterOnLocationRepository $monsterRepo,
        private AggressionChecker $aggressionChecker,
        private BossRespawnService $bossRespawnService,
    ) {}

    public function spawnAndGetAggressive(Location $location, ?int $dungeonSessionId = null): Collection
    {
        $this->bossRespawnService->respawnIfDue($location, $dungeonSessionId);

        if (! $this->canRespawn($location)) {
            return collect();
        }

        $query = MonsterOnLocation::with(['monster'])
            ->where('location_id', $location->id)
            ->where('active', 1);

        if ($dungeonSessionId !== null) {
            $query->where('dungeon_session_id', $dungeonSessionId);
        } else {
            $query->whereNull('dungeon_session_id');
        }

        $existing = $query->get();
        $aggressive = $this->aggressionChecker->getAggressive($existing);

        if ($aggressive->isNotEmpty()) {
            return $aggressive;
        }

        return $this->spawnNewAndGetAggressive($location, $dungeonSessionId);
    }

    private function canRespawn(Location $location): bool
    {
        if (! $location->last_respawn_monster_at) {
            return true;
        }

        $secondsSinceRespawn = now()->timestamp - $location->last_respawn_monster_at->timestamp;

        return $location->time_not_attack > 0 && $secondsSinceRespawn > $location->time_not_attack;
    }

    private function spawnNewAndGetAggressive(Location $location, ?int $dungeonSessionId = null): Collection
    {
        if (! $this->shouldSpawn($location)) {
            return collect();
        }

        $currentCount = $dungeonSessionId !== null
            ? $location->monstersOnLocation()->where('dungeon_session_id', $dungeonSessionId)->count()
            : $location->monstersOnLocation()->whereNull('dungeon_session_id')->count();

        $maxAdd = $location->count_monster - $currentCount;
        if ($maxAdd <= 0) {
            return collect();
        }

        $toAdd = mt_rand(1, $maxAdd);
        $available = $location->monsters->reject(fn (Monster $monster) => $monster->isBoss());

        if ($available->isEmpty()) {
            return collect();
        }

        $spawned = collect();
        for ($i = 0; $i < $toAdd; $i++) {
            $monster = $available->random();
            $locMonster = $this->monsterRepo->createMonsterOnLocation($monster, $location, $dungeonSessionId, $monster->pivot->aggression ?? null);
            $spawned->push($locMonster);
        }

        return $this->aggressionChecker->getAggressive($spawned);
    }

    private function shouldSpawn(Location $location): bool
    {
        return $location->percent_respawn_monster > 0 &&
            mt_rand(0, 100) < $location->percent_respawn_monster;
    }
}
