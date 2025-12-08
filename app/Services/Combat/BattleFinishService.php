<?php

namespace App\Services\Combat;

use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Location;
use App\Repositories\BattleRepository;
use App\Services\DropService;
use Carbon\Carbon;

readonly class BattleFinishService
{
    public function __construct(
        private BattleRepository $battleRepository,
        private DropService $dropService
    ) {
    }

    public function checkAndFinish(Battle $battle, Location $location): object
    {
        $active = BattleDetail::where('battle_id', $battle->id)
            ->whereHas('locationMonster', fn($q) => $q->where('active', 1))
            ->exists();

        if ($active) {
            return (object)['battle' => $battle];
        }

        $battle->rounds++;
        $battle->save();

        $battle = $this->battleRepository->finishBattle($battle->id);

        $this->dropService->dropItemsFromMonsters($battle, $location);

        $location->last_respawn_monster_at = Carbon::now();
        $location->save();

        foreach ($battle->detailsWithUsers as $userInBattle) {
            if ($userInBattle->status === 1) {
                $userInBattle->user->player->victory++;
                $userInBattle->user->player->save();
            }
        }

        return (object)['battle' => $battle];
    }
}
