<?php

namespace App\Services;

use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Location;
use App\Models\Monster\Monster;
use App\Models\Monster\MonsterOnLocation;
use App\Repositories\BattleRepository;
use App\Repositories\MonsterOnLocationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BattleService
{
    public function __construct(
        readonly private BattleRepository $battleRepository,
        readonly private MonsterOnLocationRepository $monsterOnLocationRepository,
    ) {}

    public function battleOnLocation(Location $location): ?Battle
    {
        $user = Auth::user();
        $now = Carbon::now();

        $battle = $this->battleRepository->findActiveBattleOnLocation($location);
        if ($battle instanceof Battle) {
            $battleDetails = BattleDetail::where(['user_id' => $user->id, 'battle_id' => $battle->id])->first();
            if (!$battleDetails instanceof BattleDetail) {
                $this->battleRepository->createBattleDetails($battle, $user);

                $action = "<p><span class='text-red'><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>";
                $this->battleRepository->createBattleRound($battle, $action, $user);
            }
        } else {
            $monsterOnLocation = MonsterOnLocation::with(['monster'])
                ->where(['location_id' => $location->id, 'active' => 1])
                ->get()
                ->filter(function ($monster) {
                    return mt_rand(0, 100) < $monster->monster->aggression;
                });

            $checkTimeRespawnMonster = $location->time_not_attack > 0
                && ($now->timestamp - $location->time_not_attack) > $location->last_respawn_monster_at?->timestamp;

            if ($monsterOnLocation->count() && $checkTimeRespawnMonster) {

                $battle = $this->battleRepository->createBattle($location);

                $this->battleRepository->createBattleDetails($battle, $user);

                foreach ($monsterOnLocation as $monster) {
                    $this->battleRepository->createBattleDetails($battle, null, $monster);
                }

                $action = "<p><span class='text-red'><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>";
                $this->battleRepository->createBattleRound($battle, $action, $user);
            } else {
                if ($checkTimeRespawnMonster && $location->percent_respawn_monster > 0 && mt_rand(0, 100) < $location->percent_respawn_monster) {
                    // Определяем текущее количество монстров на локации
                    $currentMonsterCount = $location->monstersOnLocation()->count();

                    // Вычисляем, сколько монстров можно добавить
                    $maxAddableMonsters = $location->count_monster - $currentMonsterCount;
                    // Если есть место для добавления новых монстров
                    if ($maxAddableMonsters > 0) {
                        // Выбираем случайное количество монстров для добавления (но не больше максимума)
                        $numberToAdd = mt_rand(1, $maxAddableMonsters);

                        // Получаем случайные монстры из доступных для данной локации
                        $availableMonsterOnLocations = $location->monsters;
                        $monstersToAdd = collect();
                        for ($i = 1; $i <= $numberToAdd; $i++) {
                            $monstersToAdd->add($availableMonsterOnLocations->random());
                        }

                        $aggressionToUser = collect();
                        foreach ($monstersToAdd as $agrMonster) {
                            if ($agrMonster instanceof Monster) {
                                $monsterOnLocation = $this->monsterOnLocationRepository->createMonsterOnLocation($agrMonster, $location);
                                if (mt_rand(0, 100) < $agrMonster->aggression) {
                                    $aggressionToUser->add($monsterOnLocation);
                                }
                            }
                        }

                        if ($aggressionToUser->count()) {
                            $battle = $this->battleRepository->createBattle($location);

                            $this->battleRepository->createBattleDetails($battle, $user);

                            foreach ($aggressionToUser as $monster) {
                                $this->battleRepository->createBattleDetails($battle, null, $monster);
                            }

                            $action = "<p><span class='text-red'><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>";
                            $this->battleRepository->createBattleRound($battle, $action, $user);
                        }
                    }
                }
            }
        }

        return $battle;
    }

    public function attackMonster(Location $location, int $id): ?Battle
    {
        $monsterAttacked = MonsterOnLocation::with(['monster'])->where(['id' => $id, 'active' => 1])->first();

        if ($monsterAttacked instanceof MonsterOnLocation) {
            $user = Auth::user();

            $battle = $this->battleRepository->createBattle($location);
            $this->battleRepository->createBattleDetails($battle, $user);

            $this->battleRepository->createBattleDetails($battle, null, $monsterAttacked);

            $action = sprintf("<p>Вы напали на врага - <b>%s...</b></p>", $monsterAttacked->monster->name);
            $this->battleRepository->createBattleRound($battle, $action, $user);
        }

        return $battle ?? null;
    }
}
