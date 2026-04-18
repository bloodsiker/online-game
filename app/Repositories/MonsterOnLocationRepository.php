<?php

namespace App\Repositories;

use App\Models\Monster\Monster;
use App\Models\Monster\MonsterOnLocation;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Eloquent\Collection;

class MonsterOnLocationRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return MonsterOnLocation::class;
    }

    private function query()
    {
        return $this->model->query();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query()->select(['monster_on_locations.*']);
    }

    public function getMonstersOnLocation(Location $location): Collection
    {
        return $this->getQuery()->with(['monster'])->where(['location_id' => $location->id, 'active' => 1])->get();
    }

    public function createMonsterOnLocation(Monster $monster, Location $location, ?int $dungeonSessionId = null, ?int $aggressionOverride = null): MonsterOnLocation
    {
        $monsterOnLocation = new MonsterOnLocation;
        $monsterOnLocation->monster_id = $monster->id;
        $monsterOnLocation->location_id = $location->id;
        $monsterOnLocation->dungeon_session_id = $dungeonSessionId;
        $monsterOnLocation->hp_now = $monster->hp;
        $monsterOnLocation->hp_max = $monster->hp;
        $monsterOnLocation->active = 1;
        $monsterOnLocation->aggression = $aggressionOverride;
        $monsterOnLocation->save();

        return $monsterOnLocation;
    }
}
