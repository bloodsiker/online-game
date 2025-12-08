<?php

namespace App\Services\Battle;

use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Location;
use App\Repositories\BattleRepository;

class BattleFinder
{
    public function __construct(
        private BattleRepository $battleRepository,
    ) {}

    public function findActiveForPlayer(Location $location): ?Battle
    {
        $user = auth()->user();
        $battle = $this->battleRepository->findActiveBattleOnLocation($location);

        if (!$battle) {
            return null;
        }

        $detail = BattleDetail::where(['user_id' => $user->id, 'battle_id' => $battle->id])->first();

        if (!$detail) {
            $this->battleRepository->createBattleDetails($battle, $user);
            $this->addJoinMessage($battle, $user);
        }

        return $battle;
    }

    private function addJoinMessage(Battle $battle, $user): void
    {
        $action = "<p><span class='text-red'><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>";
        $this->battleRepository->createBattleRound($battle, $action, $user);
    }
}
