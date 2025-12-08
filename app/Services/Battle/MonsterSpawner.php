<?php

namespace App\Services\Battle;

use App\Models\Location;
use App\Models\Monster\MonsterOnLocation;
use App\Repositories\MonsterOnLocationRepository;
use Illuminate\Support\Collection;

readonly class MonsterSpawner
{
    public function __construct(
        private MonsterOnLocationRepository $monsterRepo,
        private AggressionChecker $aggressionChecker,
    ) {}

    public function spawnAndGetAggressive(Location $location): Collection
    {
        if (!$this->canRespawn($location)) {
            return collect();
        }

        $existing = MonsterOnLocation::with(['monster'])
            ->where(['location_id' => $location->id, 'active' => 1])
            ->get();
        $aggressive = $this->aggressionChecker->getAggressive($existing);

        if ($aggressive->isNotEmpty()) {
            return $aggressive;
        }

        return $this->spawnNewAndGetAggressive($location);
    }

    private function canRespawn(Location $location): bool
    {
        if (!$location->last_respawn_monster_at) {
            return true;
        }

        $secondsSinceRespawn = now()->timestamp - $location->last_respawn_monster_at->timestamp;
        return $location->time_not_attack > 0 && $secondsSinceRespawn > $location->time_not_attack;
    }

    private function spawnNewAndGetAggressive(Location $location): Collection
    {
        if (!$this->shouldSpawn($location)) {
            return collect();
        }

        $currentCount = $location->monstersOnLocation()->count();
        $maxAdd = $location->count_monster - $currentCount;
        if ($maxAdd <= 0) {
            return collect();
        }

        $toAdd = mt_rand(1, $maxAdd);
        $available = $location->monsters;

        $spawned = collect();
        for ($i = 0; $i < $toAdd; $i++) {
            $monster = $available->random();
            $locMonster = $this->monsterRepo->createMonsterOnLocation($monster, $location);
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
