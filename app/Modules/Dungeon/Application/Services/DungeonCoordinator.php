<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Dungeon\Domain\Contracts\DungeonCooldownRepository;
use App\Modules\Dungeon\Domain\Contracts\DungeonSessionRepository;
use App\Modules\Dungeon\Domain\Contracts\TransactionManager;
use App\Modules\Dungeon\Domain\Enums\DungeonCooldownType;
use App\Modules\Dungeon\Domain\Enums\DungeonDeathBehavior;
use App\Modules\Dungeon\Domain\Enums\DungeonRunStatus;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonRunParticipant;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Party\Infrastructure\Persistence\Models\Party;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
use App\Modules\User\Domain\Events\UserLocationChanged;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DungeonCoordinator
{
    public function __construct(
        private readonly DungeonCooldownRepository $cooldownRepository,
        private readonly DungeonSessionRepository $sessionRepository,
        private readonly BackpackService $backpackService,
        private readonly DungeonStageService $stageService,
        private readonly DungeonRewardService $rewardService,
        private readonly TransactionManager $transactionManager,
        private readonly DetentionService $detentionService,
    ) {}

    public function enterSolo(Dungeon $dungeon, User $user): DungeonSession
    {
        $this->validateEntry($dungeon, $user);

        return $this->transactionManager->run(function () use ($dungeon, $user) {
            $this->consumeEntryKey($dungeon, $user);
            $this->applyPersonalCooldown($dungeon, $user->id);
            $this->applyGlobalCooldown($dungeon);
            $run = $this->createRun($dungeon, $user->id);
            $session = $this->createSession($dungeon, $user->id, runId: $run->id);
            $this->addParticipant($run, $session);

            if ($dungeon->isTower()) {
                $this->stageService->start($run, $session, collect([$user]));
            } else {
                $this->teleportUser($user, $dungeon->first_location_id);
                $this->spawnMonstersForSession($dungeon, $session->id);
            }

            return $session;
        });
    }

    public function resumeExistingSessionIfAllowed(Dungeon $dungeon, User $user): ?DungeonSession
    {
        $session = $this->sessionRepository->findByUserId($user->id);

        if ($session === null) {
            return null;
        }

        if ($session->isExpired()) {
            $this->expireSessionIfNeeded($user);

            return null;
        }

        if ($session->dungeon_id !== $dungeon->id) {
            throw new RuntimeException('Вы уже находитесь в другом данже.');
        }

        $user->loadMissing('currentLocation');
        $isOutsideDungeon = (int) $user->currentLocation?->dungeon_id !== (int) $session->dungeon_id;

        if ($dungeon->death_behavior !== DungeonDeathBehavior::KICK_CAN_REENTER && ! $isOutsideDungeon) {
            throw new RuntimeException('Вы уже находитесь в данже.');
        }

        $returnLocationId = $dungeon->first_location_id;
        if ($returnLocationId === null) {
            throw new RuntimeException('Данж ещё не настроен (нет первой локации).');
        }

        $this->transactionManager->run(function () use ($user, $returnLocationId) {
            $this->teleportUser($user, $returnLocationId);
        });

        return $session;
    }

    public function enterWithParty(Dungeon $dungeon, User $leader, Party $party): DungeonSession
    {
        if (! $party->isLeader($leader->id)) {
            throw new RuntimeException('Только лидер группы может войти в данж.');
        }

        $participants = $party->members->pluck('user');

        foreach ($participants as $user) {
            $this->validateEntry($dungeon, $user);
        }

        return $this->transactionManager->run(function () use ($dungeon, $leader, $participants) {
            foreach ($participants as $user) {
                $this->consumeEntryKey($dungeon, $user);
            }
            $this->applyGlobalCooldown($dungeon);
            $run = $this->createRun($dungeon, $leader->id);
            $this->applyPersonalCooldown($dungeon, $leader->id);
            $leaderSession = $this->createSession($dungeon, $leader->id, runId: $run->id);
            $this->addParticipant($run, $leaderSession);

            foreach ($participants as $user) {
                if ($user->id === $leader->id) {
                    continue;
                }

                $this->applyPersonalCooldown($dungeon, $user->id);
                $session = $this->createSession($dungeon, $user->id, $leaderSession->id, $run->id);
                $this->addParticipant($run, $session);
            }

            if ($dungeon->isTower()) {
                $this->stageService->start($run, $leaderSession, $participants);
            } else {
                foreach ($participants as $user) {
                    $this->teleportUser($user, $dungeon->first_location_id);
                }
                $this->spawnMonstersForSession($dungeon, $leaderSession->id);
            }

            return $leaderSession;
        });
    }

    public function exitDungeon(User $user): void
    {
        $session = $this->sessionRepository->findByUserId($user->id);

        if ($session === null) {
            return;
        }

        $returnLocationId = $session->dungeon->return_location_id ?? 6;

        $this->transactionManager->run(function () use ($user, $session, $returnLocationId) {
            $this->teleportUser($user, $returnLocationId);
            $this->markParticipantFinished($session, 'abandoned');
            $this->cleanupSessionMonsters($session);
            $this->sessionRepository->delete($session);
        });
    }

    public function expireSessionIfNeeded(User $user): bool
    {
        $observed = $this->sessionRepository->findByUserId($user->id);
        if ($observed === null) {
            return false;
        }
        $ids = $this->sessionUserIds($observed);

        return $this->transactionManager->run(function () use ($user, $observed, $ids): bool {
            $this->lockSessionUsers($ids);
            $battleQuery = Battle::whereIn('id',
                BattleDetail::whereIn('user_id', $ids)->select('battle_id'));
            if ($observed->dungeon_run_id !== null) {
                $battleQuery->where('dungeon_run_id', $observed->dungeon_run_id);
            } else {
                $battleQuery->whereHas('details', fn ($detail) => $detail->whereHas('locationMonster',
                    fn ($spawn) => $spawn->where('dungeon_session_id', $observed->monsterSessionId())));
            }
            $battles = $battleQuery->orderBy('id')->lockForUpdate()->get();
            if ($observed->dungeon_run_id !== null) {
                DungeonRun::whereKey($observed->dungeon_run_id)->lockForUpdate()->first();
            }
            $session = DungeonSession::with(['dungeon', 'run'])->whereKey($observed->id)->lockForUpdate()->first();
            $stageExpired = $session?->run?->status === DungeonRunStatus::ACTIVE
                && $session->run->stage_expires_at !== null && now()->gt($session->run->stage_expires_at);
            if ($session === null || (! $session->isExpired() && ! $stageExpired)) {
                return false;
            }
            $returnLocationId = $session->dungeon->return_location_id ?? 6;
            $activeIds = $session->dungeon_run_id === null ? [$session->user_id] : DungeonSession::where('dungeon_run_id', $session->dungeon_run_id)->pluck('user_id')->all();
            foreach ($battles as $battle) {
                BattleDetail::where('battle_id', $battle->id)->whereIn('user_id', $activeIds)
                    ->update(['status' => BattleDetailStatus::DEATH]);
                if (! BattleDetail::where('battle_id', $battle->id)
                    ->whereNotNull('user_id')->where('status', BattleDetailStatus::LIFE)->exists()) {
                    $battle->update(['status' => BattleStatus::FINISH]);
                }
            }
            if ($session->dungeon_run_id !== null) {
                $root = DungeonSession::where('dungeon_run_id', $session->dungeon_run_id)->whereNull('primary_session_id')->lockForUpdate()->first() ?? $session;
                $this->markRunFailed($session, 'Время этапа истекло.');
                $this->cleanupSessionMonsters($root, force: true);
                User::whereIn('id', $activeIds)->update(['prev_location_id' => DB::raw('location_id'), 'location_id' => $returnLocationId]);
                DungeonSession::where('dungeon_run_id', $session->dungeon_run_id)->delete();
            } else {
                $lockedUser = User::without('player')->findOrFail($user->id);
                $this->teleportUser($lockedUser, $returnLocationId);
                $this->cleanupSessionMonsters($session);
                $this->sessionRepository->delete($session);
            }
            DB::afterCommit(function () use ($activeIds): void {
                foreach ($activeIds as $id) {
                    UserLocationChanged::dispatch((int) $id);
                }
            });
            $user->location_id = $returnLocationId;

            return true;
        });
    }

    public function handlePlayerDeath(Player $player): ?string
    {
        $player->loadMissing('user');
        $user = $player->user;

        if ($user === null) {
            return null;
        }

        $session = $this->sessionRepository->findByUserId($user->id);

        if ($session === null) {
            return null;
        }

        if ($session->isExpired()) {
            $this->expireSessionIfNeeded($user);

            return 'Время похода истекло. Вы перенесены из данжа.';
        }

        $dungeon = $session->dungeon;

        return match ($dungeon->death_behavior) {
            DungeonDeathBehavior::EXIT => $this->handleDeathExit($user, $session),
            DungeonDeathBehavior::RETURN_TO_START => $this->handleDeathReturnToStart($user, $dungeon),
            DungeonDeathBehavior::KICK_CAN_REENTER => $this->handleDeathKickCanReenter($user, $dungeon),
        };
    }

    public function tryAdvanceSurvivalWave(DungeonSession $session, Location $location): void
    {
        $ids = $this->sessionUserIds($session);
        $this->transactionManager->run(function () use ($session, $location, $ids): void {
            $this->lockSessionUsers($ids);
            if ($session->dungeon_run_id !== null) {
                $run = DungeonRun::whereKey($session->dungeon_run_id)->lockForUpdate()->first();
                if ($run === null || $run->status !== DungeonRunStatus::ACTIVE) {
                    return;
                }
            }
            $session = DungeonSession::whereKey($session->monsterSessionId())->lockForUpdate()->first();
            if ($session === null || $session->isCompleted() || $session->isExpired()) {
                return;
            }
            $this->advanceSurvivalWaveLocked($session, $location);
        });
    }

    private function advanceSurvivalWaveLocked(DungeonSession $session, Location $location): void
    {
        $dungeon = $location->dungeon;

        if ($dungeon === null || ! $dungeon->isSurvival() || $dungeon->wave_count === null) {
            return;
        }

        if ($location->id !== $dungeon->first_location_id) {
            return;
        }

        $dungeonSessionId = $session->monsterSessionId();

        $hasActive = MonsterOnLocation::query()
            ->where('location_id', $location->id)
            ->where('dungeon_session_id', $dungeonSessionId)
            ->where('active', 1)
            ->exists();

        if ($hasActive) {
            return;
        }

        if ($session->current_wave >= $dungeon->wave_count) {
            if ($session->dungeon_run_id !== null) {
                $this->completeSharedRun($session);
            } elseif ($this->sessionRepository->markCompleted($session)) {
                $this->rewardService->grant($dungeon, $session->user);
            }

            return;
        }

        $session = $this->sessionRepository->incrementWave($session);
        if ($session->dungeon_run_id !== null) {
            DungeonSession::where('dungeon_run_id', $session->dungeon_run_id)->where('id', '!=', $session->id)->update(['current_wave' => $session->current_wave]);
        }

        $location->load('monsters');
        $this->spawnWaveOnLocation($location, $dungeonSessionId);
    }

    private function sessionUserIds(DungeonSession $session): array
    {
        return ($session->dungeon_run_id === null ? DungeonSession::where('id', $session->monsterSessionId())->orWhere('primary_session_id', $session->monsterSessionId())->pluck('user_id') : DungeonRunParticipant::where('dungeon_run_id', $session->dungeon_run_id)->pluck('user_id')->push($session->user_id))
            ->filter()->unique()->sort()->values()->all();
    }

    private function lockSessionUsers(array $ids): void
    {
        foreach ($ids as $id) {
            User::without('player')->whereKey($id)->lockForUpdate()->firstOrFail();
        }
    }

    public function spawnWaveOnLocation(Location $location, int $sessionId): void
    {
        $pool = $location->monsters;
        $count = max(1, $location->count_monster);

        if ($pool->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $monster = $pool->random();
            MonsterOnLocation::query()->create([
                'location_id' => $location->id,
                'dungeon_session_id' => $sessionId,
                'monster_id' => $monster->id,
                'hp_now' => $monster->hp,
                'hp_max' => $monster->hp,
                'active' => 1,
                'aggression' => $monster->pivot->aggression ?? null,
            ]);
        }
    }

    private function spawnMonstersForSession(Dungeon $dungeon, int $sessionId): void
    {
        if ($dungeon->isSurvival()) {
            $location = Location::query()->with('monsters')->find($dungeon->first_location_id);
            if ($location !== null) {
                $this->spawnWaveOnLocation($location, $sessionId);
            }

            return;
        }

        $locations = Location::query()
            ->where('dungeon_id', $dungeon->id)
            ->with('monsters')
            ->get();

        foreach ($locations as $location) {
            foreach ($location->monsters as $monster) {
                MonsterOnLocation::query()->create([
                    'location_id' => $location->id,
                    'dungeon_session_id' => $sessionId,
                    'monster_id' => $monster->id,
                    'hp_now' => $monster->hp,
                    'hp_max' => $monster->hp,
                    'active' => 1,
                    'aggression' => $monster->pivot->aggression ?? null,
                ]);
            }
        }
    }

    private function handleDeathExit(User $user, DungeonSession $session): string
    {
        $returnLocationId = $session->dungeon->return_location_id ?? 6;

        $this->transactionManager->run(function () use ($user, $session, $returnLocationId) {
            $this->teleportUser($user, $returnLocationId);
            $this->cleanupSessionMonsters($session);
            $this->sessionRepository->delete($session);
        });

        return 'Смерть завершила поход. Вы выброшены из данжа.';
    }

    private function handleDeathReturnToStart(User $user, Dungeon $dungeon): string
    {
        $returnLocationId = $dungeon->death_return_location_id
            ?? $dungeon->first_location_id
            ?? $dungeon->return_location_id
            ?? 6;

        $this->transactionManager->run(function () use ($user, $returnLocationId) {
            $this->teleportUser($user, $returnLocationId);
        });

        return 'После смерти вы вернулись к началу данжа.';
    }

    private function handleDeathKickCanReenter(User $user, Dungeon $dungeon): string
    {
        $returnLocationId = $dungeon->death_return_location_id
            ?? $dungeon->return_location_id
            ?? 6;

        $this->transactionManager->run(function () use ($user, $returnLocationId) {
            $this->teleportUser($user, $returnLocationId);
        });

        return 'Вы выброшены из данжа, но можете вернуться, пока не истекло время похода.';
    }

    private function cleanupSessionMonsters(DungeonSession $session, bool $force = false): void
    {
        $monsterSessionId = $session->monsterSessionId();

        if (! $force && $session->primary_session_id !== null) {
            return;
        }

        if (! $force && $this->sessionRepository->hasFollowers($session->id)) {
            return;
        }

        MonsterOnLocation::query()->where('dungeon_session_id', $monsterSessionId)->delete();
        $this->cleanupDungeonLocationItems($session->dungeon_id, $monsterSessionId);
    }

    private function cleanupDungeonLocationItems(int $dungeonId, int $dungeonSessionId): void
    {
        $locationIds = Location::query()->where('dungeon_id', $dungeonId)->pluck('id');

        if ($locationIds->isEmpty()) {
            return;
        }

        $itemIds = DB::table('item_on_locations')
            ->whereIn('location_id', $locationIds)
            ->where('dungeon_session_id', $dungeonSessionId)
            ->pluck('item_id');

        DB::table('item_on_locations')
            ->whereIn('location_id', $locationIds)
            ->where('dungeon_session_id', $dungeonSessionId)
            ->delete();

        if ($itemIds->isNotEmpty()) {
            DB::table('items')->whereIn('id', $itemIds)->delete();
        }
    }

    private function validateEntry(Dungeon $dungeon, User $user): void
    {
        if ($detentionMessage = $this->detentionService->blockLeaving($user)) {
            throw new RuntimeException($detentionMessage);
        }

        if (! $dungeon->is_active) {
            throw new RuntimeException('Данж недоступен.');
        }

        if ($dungeon->first_location_id === null) {
            throw new RuntimeException('Данж ещё не настроен (нет первой локации).');
        }

        $player = $user->player;
        if ($player->lvl < $dungeon->min_level) {
            throw new RuntimeException('Ваш уровень слишком низкий для этого данжа.');
        }

        if ($this->sessionRepository->existsForUser($user->id)) {
            throw new RuntimeException('Вы уже находитесь в данже.');
        }

        if ($dungeon->entry_location_id !== null && $user->location_id !== $dungeon->entry_location_id) {
            throw new RuntimeException('Чтобы войти, вы должны быть у входа в данж.');
        }

        if ($this->cooldownRepository->isPersonalOnCooldown($dungeon->id, $user->id)) {
            throw new RuntimeException('Данж ещё на перезарядке.');
        }

        if ($dungeon->hasGlobalCooldown() && $this->cooldownRepository->isGlobalOnCooldown($dungeon->id)) {
            throw new RuntimeException('Данж временно недоступен (глобальный кулдаун).');
        }

        if ($dungeon->requiresKey()) {
            $hasKey = $this->backpackService->getItem($user, $dungeon->entryItem) !== null;
            if (! $hasKey) {
                throw new RuntimeException('Нет ключа для входа в этот данж.');
            }
        }
    }

    private function consumeEntryKey(Dungeon $dungeon, User $user): void
    {
        if ($dungeon->requiresKey()) {
            $this->backpackService->removeItemByShareItem($user, $dungeon->entryItem, 1);
        }
    }

    private function applyPersonalCooldown(Dungeon $dungeon, int $userId): void
    {
        if ($dungeon->cooldown_type === DungeonCooldownType::PERSONAL) {
            $this->cooldownRepository->setPersonal(
                $dungeon->id,
                $userId,
                now()->addSeconds($dungeon->cooldown_seconds),
            );
        }
    }

    private function applyGlobalCooldown(Dungeon $dungeon): void
    {
        if ($dungeon->hasGlobalCooldown()) {
            $this->cooldownRepository->setGlobal($dungeon->id, now()->addSeconds($dungeon->cooldown_seconds));
        }
    }

    private function createSession(Dungeon $dungeon, int $userId, ?int $primarySessionId = null, ?int $runId = null): DungeonSession
    {
        $expiresAt = $dungeon->hasDungeonTimer()
            ? Carbon::now()->addSeconds($dungeon->time_limit_seconds)
            : null;

        return $this->sessionRepository->create($dungeon, $userId, $expiresAt, $primarySessionId, $runId);
    }

    private function createRun(Dungeon $dungeon, int $leaderUserId): DungeonRun
    {
        return DungeonRun::query()->create([
            'dungeon_id' => $dungeon->id,
            'leader_user_id' => $leaderUserId,
            'status' => DungeonRunStatus::ACTIVE,
            'started_at' => now(),
            'expires_at' => $dungeon->hasDungeonTimer() ? now()->addSeconds($dungeon->time_limit_seconds) : null,
        ]);
    }

    private function addParticipant(DungeonRun $run, DungeonSession $session): void
    {
        DungeonRunParticipant::query()->create([
            'dungeon_run_id' => $run->id,
            'user_id' => $session->user_id,
            'dungeon_session_id' => $session->id,
            'status' => 'active',
        ]);
    }

    private function markParticipantFinished(DungeonSession $session, string $status): void
    {
        if ($session->dungeon_run_id === null) {
            return;
        }

        DungeonRunParticipant::query()
            ->where('dungeon_run_id', $session->dungeon_run_id)
            ->where('user_id', $session->user_id)
            ->update(['status' => $status, 'finished_at' => now()]);

        $hasActive = DungeonRunParticipant::query()
            ->where('dungeon_run_id', $session->dungeon_run_id)
            ->where('status', 'active')
            ->exists();
        if (! $hasActive) {
            DungeonRun::query()->whereKey($session->dungeon_run_id)->update([
                'status' => DungeonRunStatus::ABANDONED->value,
                'finished_at' => now(),
            ]);
        }
    }

    private function markRunFailed(DungeonSession $session, string $reason): void
    {
        if ($session->dungeon_run_id === null) {
            return;
        }

        $failed = DungeonRun::query()->whereKey($session->dungeon_run_id)->where('status', DungeonRunStatus::ACTIVE)->update([
            'status' => DungeonRunStatus::FAILED->value,
            'failure_reason' => $reason,
            'finished_at' => now(),
        ]);
        if ($failed === 0) {
            return;
        }
        DungeonRunParticipant::query()->where('dungeon_run_id', $session->dungeon_run_id)->update([
            'status' => 'failed',
            'finished_at' => now(),
        ]);
    }

    private function completeSharedRun(DungeonSession $session): void
    {
        $this->transactionManager->run(function () use ($session): void {
            $completed = DungeonRun::query()
                ->whereKey($session->dungeon_run_id)
                ->where('status', DungeonRunStatus::ACTIVE->value)
                ->update([
                    'status' => DungeonRunStatus::COMPLETED->value,
                    'finished_at' => now(),
                    'stage_expires_at' => null,
                ]);

            if ($completed === 0) {
                return;
            }

            $run = DungeonRun::query()->with(['dungeon', 'participants.user'])->findOrFail($session->dungeon_run_id);
            $run->participants()->update(['status' => 'completed', 'finished_at' => now()]);
            $run->sessions()->update(['completed_at' => now()]);
            foreach ($run->participants as $participant) {
                if ($participant->user !== null) {
                    $this->rewardService->grant($run->dungeon, $participant->user);
                }
            }
        });
    }

    private function teleportUser(User $user, int $locationId): void
    {
        $user->location_id = $locationId;
        $user->save();
    }
}
