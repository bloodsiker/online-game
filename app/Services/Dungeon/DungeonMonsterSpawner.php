<?php

declare(strict_types=1);

namespace App\Services\Dungeon;

use App\Enums\BattleStatus;
use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Dungeon\DungeonFloor;
use App\Models\Dungeon\DungeonRunFloor;
use App\Models\Monster\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DungeonMonsterSpawner
{
    /**
     * Spawn monsters for a dungeon floor and create a battle.
     * Returns the created Battle.
     *
     * @param  Collection<int, User>  $participants
     */
    public function spawnFloor(DungeonRunFloor $runFloor, DungeonFloor $floor, Collection $participants): Battle
    {
        return DB::transaction(function () use ($runFloor, $floor, $participants) {
            // Create a dungeon battle (no location, linked to run floor)
            $battle = Battle::create([
                'dungeon_run_floor_id' => $runFloor->id,
                'status' => BattleStatus::ACTIVE,
                'rounds' => 1,
            ]);

            // Link battle back to run floor
            $runFloor->update(['battle_id' => $battle->id]);

            // Spawn monsters from pool
            $pool = $floor->monsterPool()->inRandomOrder()->get();

            foreach ($pool as $poolEntry) {
                $monster = $poolEntry->monster;

                /** @var MonsterOnLocation $mol */
                $mol = MonsterOnLocation::create([
                    'location_id' => null,
                    'dungeon_run_floor_id' => $runFloor->id,
                    'monster_id' => $monster->id,
                    'hp_max' => $monster->hp,
                    'hp_now' => $monster->hp,
                    'active' => 1,
                    'is_drop_money' => 0,
                    'current_phase' => 0,
                ]);

                BattleDetail::create([
                    'battle_id' => $battle->id,
                    'location_monster_id' => $mol->id,
                ]);
            }

            // Add all participants to the battle
            foreach ($participants as $user) {
                BattleDetail::create([
                    'battle_id' => $battle->id,
                    'user_id' => $user->id,
                ]);
            }

            return $battle;
        });
    }
}
