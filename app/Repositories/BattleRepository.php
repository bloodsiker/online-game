<?php

namespace App\Repositories;

use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Battle\BattleRound;
use App\Models\Location;
use App\Models\Monster\MonsterOnLocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BattleRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Battle::class;
    }

    private function query()
    {
        return $this->model->query();
    }

    public function getQuery()
    {
        $query = $this->model->query();

        return $query->select(['battles.*']);
    }

    public function findActiveBattleOnLocation(Location $location): ?Battle
    {
        return $this->getQuery()->where(['location_id' => $location->id, 'status' => Battle::STATUS_ACTIVE])->first();
    }

    public function finishBattle(int $id): Battle
    {
        return $this->update(['status' => Battle::STATUS_FINISH], $id);
    }

    public function createBattle(Location $location): Battle
    {
        return $this->create([
            'location_id' => $location->id,
            'status' => Battle::STATUS_ACTIVE,
            'rounds' => 1,
        ]);
    }

    public function createBattleDetails(Battle $battle, ?User $user = null, ?MonsterOnLocation $monsterOnLocation = null): BattleDetail
    {
        $battleDetails = new BattleDetail();
        $battleDetails->battle_id = $battle->id;

        $battleDetails->status = BattleDetail::STATUS_LIFE;
        if ($user instanceof User) {
            $battleDetails->user_id = $user->id;
        }

        if ($monsterOnLocation instanceof MonsterOnLocation) {
            $battleDetails->location_monster_id = $monsterOnLocation->id;
        }

        $battleDetails->save();

        return $battleDetails;
    }

    public function createBattleRound(Battle $battle, string $action, User $user): BattleRound
    {
        return DB::transaction(function () use ($battle, $user, $action) {
            $battle->increment('rounds');

            $round = BattleRound::create([
                'battle_id'    => $battle->id,
                'round_number' => $battle->rounds,
                'user_id'      => $user->id,
                'action'       => $action,
            ]);

            if (!$round) {
                throw new \RuntimeException('Failed to create battle round');
            }

            return $round;
        });
    }
}
