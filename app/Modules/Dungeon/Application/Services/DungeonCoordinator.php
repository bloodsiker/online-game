<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Services;

use App\Models\Party\Party;
use App\Modules\Backpack\Domain\Services\BackpackService;
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
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
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
        $session = $this->sessionRepository->findByUserId($user->id);

        $stageExpired = $session?->run?->status === DungeonRunStatus::ACTIVE
            && $session->run->stage_expires_at !== null
            && now()->gt($session->run->stage_expires_at);

        if ($session === null || (! $session->isExpired() && ! $stageExpired)) {
            return false;
        }

        $returnLocationId = $session->dungeon->return_location_id ?? 6;

        $this->transactionManager->run(function () use ($user, $session, $returnLocationId) {
            if ($session->dungeon_run_id !== null) {
                $rootSession = DungeonSession::query()
                    ->where('dungeon_run_id', $session->dungeon_run_id)
                    ->whereNull('primary_session_id')
                    ->first() ?? $session;
                $participantIds = DungeonRunParticipant::query()
                    ->where('dungeon_run_id', $session->dungeon_run_id)
                    ->pluck('user_id');

                $this->markRunFailed($session, 'Время этапа истекло.');
                $this->cleanupSessionMonsters($rootSession, force: true);
                User::query()->whereIn('id', $participantIds)->update([
                    'prev_location_id' => DB::raw('location_id'),
                    'location_id' => $returnLocationId,
                ]);
                DungeonSession::query()->where('dungeon_run_id', $session->dungeon_run_id)->delete();
                $user->location_id = $returnLocationId;

                return;
            }

            $this->teleportUser($user, $returnLocationId);
            $this->cleanupSessionMonsters($session);
            $this->sessionRepository->delete($session);
        });

        return true;
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
            } elseif (! $session->isCompleted()) {
                $this->sessionRepository->markCompleted($session);
                $this->rewardService->grant($dungeon, $session->user);
            }

            return;
        }

        $session = $this->sessionRepository->incrementWave($session);

        $location->load('monsters');
        $this->spawnWaveOnLocation($location, $dungeonSessionId);
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

        DungeonRun::query()->whereKey($session->dungeon_run_id)->update([
            'status' => DungeonRunStatus::FAILED->value,
            'failure_reason' => $reason,
            'finished_at' => now(),
        ]);
        DungeonRunParticipant::query()->where('dungeon_run_id', $session->dungeon_run_id)->update([
            'status' => 'failed',
            'finished_at' => now(),
        ]);
    }

    private function completeSharedRun(DungeonSession $session): void
    {
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
    }

    private function teleportUser(User $user, int $locationId): void
    {
        $user->location_id = $locationId;
        $user->save();
    }
}
