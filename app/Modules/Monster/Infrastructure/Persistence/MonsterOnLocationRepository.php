<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;

class MonsterOnLocationRepository
{
    public function createMonsterOnLocation(
        Monster $monster,
        Location $location,
        ?int $dungeonSessionId = null,
        ?int $aggressionOverride = null,
    ): MonsterOnLocation {
        $monsterOnLocation = new MonsterOnLocation;
        $monsterOnLocation->monster_id = $monster->id;
        $monsterOnLocation->location_id = $location->id;
        $monsterOnLocation->dungeon_session_id = $dungeonSessionId;
        $monsterOnLocation->hp_now = $monster->hp;
        $monsterOnLocation->hp_max = $monster->hp;
        $monsterOnLocation->active = true;
        $monsterOnLocation->aggression = $aggressionOverride;
        $monsterOnLocation->save();

        return $monsterOnLocation;
    }
}
