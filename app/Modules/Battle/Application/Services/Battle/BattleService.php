<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Dungeon\Application\UseCases\AdvanceSurvivalWave;
use App\Modules\Dungeon\Application\UseCases\GetActiveDungeonSession;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Monster\Infrastructure\Persistence\MonsterOnLocationRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BattleService
{
    public function __construct(
        private readonly BattleRepository $battleRepository,
        private readonly MonsterOnLocationRepository $monsterOnLocationRepository,
        private readonly GetActiveDungeonSession $getActiveDungeonSession,
        private readonly AdvanceSurvivalWave $advanceSurvivalWave,
        private readonly BossRespawnService $bossRespawnService,
    ) {}

    public function battleOnLocation(Location $location): ?Battle
    {
        // Wave rewards/spawning must not acquire other participant locks while holding the entry mutex.
        if ($location->dungeon_id !== null) {
            $session = $this->getActiveDungeonSession->execute(Auth::id());
            if ($session !== null) {
                $this->handleSurvivalWave($session, $location);
            }
        }

        return $this->enterAtomically($location, fn (User $user) => $this->battleOnLocationLocked($location, $user));
    }

    private function battleOnLocationLocked(Location $location, User $user): ?Battle
    {
        $now = Carbon::now();
        $battle = null;

        // Определяем изоляцию данжа: если локация принадлежит данжу — фильтруем по сессии
        $dungeonSessionId = null;
        $dungeonRunId = null;
        $session = null;
        if ($location->dungeon_id !== null) {
            $session = $this->getActiveDungeonSession->execute($user->id);
            if ($session === null) {
                return null; // игрок в данже-локации без сессии — монстров нет
            }
            $dungeonSessionId = $session->monsterSessionId();
            $dungeonRunId = $session->dungeon_run_id;

            // Survival: если все монстры волны мертвы — спавним следующую волну
        }

        $battle = $this->battleRepository->findActiveBattleOnLocation($location, $dungeonRunId, $dungeonSessionId, forUpdate: true);
        if ($battle instanceof Battle) {
            $battleDetails = BattleDetail::where(['user_id' => $user->id, 'battle_id' => $battle->id])->first();
            if (! $battleDetails instanceof BattleDetail) {
                $this->battleRepository->createBattleDetails($battle, $user);

                $action = "<p><span class='text-red'><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>";
                $this->battleRepository->createBattleRound($battle, $action, $user);
            } elseif ($battleDetails->status->isDeath()) {
                $battleDetails->status = BattleDetailStatus::LIFE;
                $battleDetails->save();
                $this->battleRepository->createBattleRound(
                    $battle,
                    '<p><b>Игрок вернулся в бой после возрождения.</b></p>',
                    $user,
                );
            }
        } else {
            $this->bossRespawnService->respawnIfDue($location, $dungeonSessionId);

            $monsterQuery = MonsterOnLocation::with(['monster'])
                ->where('location_id', $location->id)
                ->where('active', 1)
                ->visibleTo($user);

            if ($dungeonSessionId !== null) {
                $monsterQuery->where('dungeon_session_id', $dungeonSessionId);
            } else {
                $monsterQuery->whereNull('dungeon_session_id');
            }

            $monsterOnLocation = $monsterQuery->orderBy('id')->lockForUpdate()->get()->filter(function ($monster) {
                return mt_rand(0, 100) < $monster->getAggression();
            });

            $checkTimeRespawnMonster = $location->time_not_attack > 0
                && ($now->timestamp - $location->time_not_attack) > $location->last_respawn_monster_at?->timestamp;

            if ($monsterOnLocation->count() && $checkTimeRespawnMonster) {

                $battle = $this->battleRepository->createBattle($location, $dungeonRunId);

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

                    $availableMonsterOnLocations = $location->monsters->reject(fn (Monster $monster) => $monster->isBoss());

                    if ($maxAddableMonsters > 0 && $availableMonsterOnLocations->isNotEmpty()) {
                        $numberToAdd = mt_rand(1, $maxAddableMonsters);

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
                            $battle = $this->battleRepository->createBattle($location, $dungeonRunId);

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
        return $this->enterAtomically($location, fn (User $user) => $this->attackMonsterLocked($location, $id, $user));
    }

    private function enterAtomically(Location $location, callable $enter): ?Battle
    {
        $actor = Auth::user();
        $active = $this->battleRepository->findActiveBattleOnLocation($location);
        $ids = $active === null ? [$actor->id] : app(BattleActionLocks::class)->userIds($active->id, $actor->id);

        return DB::transaction(function () use ($location, $actor, $ids, $enter): ?Battle {
            $users = app(BattleActionLocks::class)->users($ids);
            if ((int) $users->get($actor->id)->location_id !== (int) $location->id) {
                return null;
            }
            DB::table('battle_entry_locks')->insertOrIgnore(['location_id' => $location->id]);
            DB::table('battle_entry_locks')->where('location_id', $location->id)->lockForUpdate()->first();

            return $enter($users->get($actor->id));
        }, attempts: 3);
    }

    private function attackMonsterLocked(Location $location, int $id, User $user): ?Battle
    {
        $session = $location->dungeon_id !== null
            ? $this->getActiveDungeonSession->execute($user->id)
            : null;
        if ($location->dungeon_id !== null && $session === null) {
            return null;
        }
        $dungeonSessionId = $session?->monsterSessionId();

        $monsterAttacked = MonsterOnLocation::with(['monster'])
            ->where(['id' => $id, 'active' => 1])
            ->visibleTo($user)
            ->first();

        if ($monsterAttacked instanceof MonsterOnLocation
            && ((int) $monsterAttacked->location_id !== (int) $location->id
                || ($dungeonSessionId !== null && (int) $monsterAttacked->dungeon_session_id !== $dungeonSessionId)
                || ($dungeonSessionId === null && $monsterAttacked->dungeon_session_id !== null))) {
            $monsterAttacked = null;
        }

        if (! $monsterAttacked instanceof MonsterOnLocation) {
            return null;
        }

        // На локации бой может уже идти: моб напал первым или дерётся другой
        // игрок. Заводить второй бой нельзя — findActiveBattleOnLocation()
        // отдаёт старший, и удары уходят не в тот бой, что показан игроку.
        $battle = $this->battleRepository->findActiveBattleOnLocation($location, $session?->dungeon_run_id, $dungeonSessionId, forUpdate: true);

        // An existing battle is locked before the spawn. With no battle, the entry mutex
        // makes it safe to validate the locked spawn before creating any participant rows.
        $monsterAttacked = MonsterOnLocation::with('monster')->whereKey($id)->where('active', 1)->lockForUpdate()->first();
        if ($monsterAttacked === null || $monsterAttacked->hp_now <= 0) {
            return null;
        }
        if ($battle instanceof Battle) {
            $this->joinExistingBattle($battle, $user);
        } else {
            $battle = $this->battleRepository->createBattle($location, $session?->dungeon_run_id);
            $this->battleRepository->createBattleDetails($battle, $user);
        }

        if (! $this->isMonsterInBattle($battle, $monsterAttacked)) {
            $this->battleRepository->createBattleDetails($battle, null, $monsterAttacked);
        }

        $action = sprintf('<p>Вы напали на врага - <b>%s...</b></p>', $monsterAttacked->monster->name);
        $this->battleRepository->createBattleRound($battle, $action, $user);

        return $battle;
    }

    /**
     * Подключает игрока к уже идущему бою: новичку заводит запись участника,
     * ранее убитому возвращает статус «жив».
     */
    private function joinExistingBattle(Battle $battle, User $user): void
    {
        $detail = BattleDetail::where(['battle_id' => $battle->id, 'user_id' => $user->id])->first();

        if (! $detail instanceof BattleDetail) {
            $this->battleRepository->createBattleDetails($battle, $user);

            return;
        }

        if ($detail->status->isDeath()) {
            $detail->status = BattleDetailStatus::LIFE;
            $detail->save();
        }
    }

    private function isMonsterInBattle(Battle $battle, MonsterOnLocation $monster): bool
    {
        return BattleDetail::where(['battle_id' => $battle->id, 'location_monster_id' => $monster->id])->exists();
    }
}
