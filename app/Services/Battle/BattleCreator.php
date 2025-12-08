<?php

namespace App\Services\Battle;

use App\Models\Battle\Battle;
use App\Models\Location;
use App\Repositories\BattleRepository;
use Illuminate\Support\Collection;

class BattleCreator
{
    public function __construct(
        private BattleRepository $battleRepository,
    ) {}

    public function createWithMonsters(Location $location, Collection $monsters): Battle
    {
        $battle = $this->battleRepository->createBattle($location);
        $user = auth()->user();

        $this->battleRepository->createBattleDetails($battle, $user);

        foreach ($monsters as $monster) {
            $this->battleRepository->createBattleDetails($battle, null, $monster);
        }

        $action = "<p><span class='text-red'><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>";
        $this->battleRepository->createBattleRound($battle, $action, $user);

        return $battle;
    }
}
