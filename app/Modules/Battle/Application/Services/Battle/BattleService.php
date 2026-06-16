<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Dungeon\Application\UseCases\AdvanceSurvivalWave;
use App\Modules\Dungeon\Application\UseCases\GetActiveDungeonSession;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Repositories\MonsterOnLocationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BattleService
{
    public function __construct(
        private readonly BattleRepository $battleRepository,
        private readonly MonsterOnLocationRepository $monsterOnLocationRepository,
        private readonly GetActiveDungeonSession $getActiveDungeonSession,
        private readonly AdvanceSurvivalWave $advanceSurvivalWave,
    ) {}

    public function battleOnLocation(Location $location): ?Battle
    {
        $user = Auth::user();
        $now = Carbon::now();
        $battle = null;

        // Определяем изоляцию данжа: если локация принадлежит данжу — фильтруем по сессии
        $dungeonSessionId = null;
        $session = null;
        if ($location->dungeon_id !== null) {
            $session = $this->getActiveDungeonSession->execute($user->id);
            if ($session === null) {
                return null; // игрок в данже-локации без сессии — монстров нет
            }
            $dungeonSessionId = $session->monsterSessionId();

            // Survival: если все монстры волны мертвы — спавним следующую волну
            $this->handleSurvivalWave($session, $location);
        }

        $battle = $this->battleRepository->findActiveBattleOnLocation($location);
        if ($battle instanceof Battle) {
            $battleDetails = BattleDetail::where(['user_id' => $user->id, 'battle_id' => $battle->id])->first();
            if (! $battleDetails instanceof BattleDetail) {
                $this->battleRepository->createBattleDetails($battle, $user);

                $action = "<p><span class='text-red'><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>";
                $this->battleRepository->createBattleRound($battle, $action, $user);
            }
        } else {
            $monsterQuery = MonsterOnLocation::with(['monster'])
                ->where('location_id', $location->id)
                ->where('active', 1);

            if ($dungeonSessionId !== null) {
                $monsterQuery->where('dungeon_session_id', $dungeonSessionId);
            } else {
                $monsterQuery->whereNull('dungeon_session_id');
            }

            $monsterOnLocation = $monsterQuery->get()->filter(function ($monster) {
                return mt_rand(0, 100) < $monster->getAggression();
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
                // Респавн: для данжа проверяем настройку monster_respawn
                $canRespawn = $dungeonSessionId === null
                    || ($location->dungeon?->monster_respawn ?? false);

                if ($canRespawn && $checkTimeRespawnMonster && $location->percent_respawn_monster > 0 && mt_rand(0, 100) < $location->percent_respawn_monster) {
                    $currentMonsterCount = $dungeonSessionId !== null
                        ? MonsterOnLocation::where('location_id', $location->id)->where('dungeon_session_id', $dungeonSessionId)->count()
                        : $location->monstersOnLocation()->count();

                    $maxAddableMonsters = $location->count_monster - $currentMonsterCount;

                    if ($maxAddableMonsters > 0) {
                        $numberToAdd = mt_rand(1, $maxAddableMonsters);

                        $availableMonsterOnLocations = $location->monsters;
                        $monstersToAdd = collect();
                        for ($i = 1; $i <= $numberToAdd; $i++) {
                            $monstersToAdd->add($availableMonsterOnLocations->random());
                        }

                        $aggressionToUser = collect();
                        foreach ($monstersToAdd as $agrMonster) {
                            if ($agrMonster instanceof Monster) {
                                $monsterOnLocation = $this->monsterOnLocationRepository->createMonsterOnLocation(
                                    $agrMonster,
                                    $location,
                                    $dungeonSessionId,
                                );
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

    /**
     * Survival: проверяет, нужно ли спавнить следующую волну.
     * Вызывается при каждом входе на локацию данжа.
     * Если активных монстров нет и волны ещё остались — спавним следующую.
     */
    private function handleSurvivalWave(DungeonSession $session, Location $location): void
    {
        $this->advanceSurvivalWave->execute($session, $location);
    }

    public function attackMonster(Location $location, int $id): ?Battle
    {
        $monsterAttacked = MonsterOnLocation::with(['monster'])->where(['id' => $id, 'active' => 1])->first();

        if ($monsterAttacked instanceof MonsterOnLocation) {
            $user = Auth::user();

            $battle = $this->battleRepository->createBattle($location);
            $this->battleRepository->createBattleDetails($battle, $user);

            $this->battleRepository->createBattleDetails($battle, null, $monsterAttacked);

            $action = sprintf('<p>Вы напали на врага - <b>%s...</b></p>', $monsterAttacked->monster->name);
            $this->battleRepository->createBattleRound($battle, $action, $user);
        }

        return $battle ?? null;
    }
}
