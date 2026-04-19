<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;

class MonsterSelector
{
    public function getRandomActiveMonster(Battle $battle): ?BattleDetail
    {
        $activeIds = BattleDetail::where(['battle_id' => $battle->id, 'status' => 1])
            ->whereNotNull('location_monster_id')
            ->pluck('location_monster_id');

        if ($activeIds->isEmpty()) {
            return null;
        }

        return BattleDetail::with('locationMonster')
            ->where('location_monster_id', $activeIds->random())
            ->where('battle_id', $battle->id)
            ->first();
    }
}
