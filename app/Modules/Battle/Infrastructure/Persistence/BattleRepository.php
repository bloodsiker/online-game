<?php

declare(strict_types=1);

namespace App\Modules\Battle\Infrastructure\Persistence;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BattleRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Battle::class;
    }

    public function getQuery(): Builder
    {
        return $this->model->query()->select(['battles.*']);
    }

    public function findActiveBattleOnLocation(Location $location): ?Battle
    {
        return $this->getQuery()->where(['location_id' => $location->id, 'status' => BattleStatus::ACTIVE])->first();
    }

    public function finishBattle(int $id): Battle
    {
        return $this->update(['status' => BattleStatus::FINISH], $id);
    }

    public function createBattle(Location $location): Battle
    {
        return $this->create([
            'location_id' => $location->id,
            'status' => BattleStatus::ACTIVE,
            'rounds' => 0,
        ]);
    }

    public function createBattleDetails(Battle $battle, ?User $user = null, ?MonsterOnLocation $monsterOnLocation = null): BattleDetail
    {
        $battleDetails = new BattleDetail;
        $battleDetails->battle_id = $battle->id;

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
                'battle_id' => $battle->id,
                'round_number' => $battle->rounds,
                'user_id' => $user->id,
                'action' => $action,
            ]);

            if (! $round) {
                throw new \RuntimeException('Failed to create battle round');
            }

            return $round;
        });
    }
}
