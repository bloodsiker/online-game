<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Dungeon\Domain\Events\ClanBossDefeated;
use App\Modules\Dungeon\Domain\Events\ClanBossRunExpired;
use App\Modules\Dungeon\Domain\Events\ClanBossRunStarted;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossContribution;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossCooldown;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossPlayerAttackState;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossDefeatLog;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Асинхронный клан-босс: общий HP на весь клан, боссы идут по очереди.
 *
 * Методы assertCanHit/recordHit/handleStageKill рассчитаны на вызов внутри
 * транзакции боя (FightOrchestrator) и сами транзакций не открывают.
 * openRun/expireOverdue открывают собственные транзакции.
 */
final class ClanBossService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ClanBossRewardService $rewardService,
    ) {}

    public function findActiveRun(Dungeon $dungeon, int $clanId): ?ClanBossRun
    {
        return ClanBossRun::query()
            ->where('dungeon_id', $dungeon->id)
            ->where('clan_id', $clanId)
            ->where('status', ClanBossRun::STATUS_ACTIVE)
            ->orderByDesc('id')
            ->first();
    }

    public function findActiveRunForClan(int $clanId): ?ClanBossRun
    {
        return ClanBossRun::query()
            ->with('dungeon')
            ->where('clan_id', $clanId)
            ->where('status', ClanBossRun::STATUS_ACTIVE)
            ->orderByDesc('id')
            ->first();
    }

    public function openRun(Structure $structure, User $user): ClanBossRun
    {
        if (! $structure->isClanBoss()) {
            throw new RuntimeException('Это не логово кланового босса.');
        }

        $structure->loadMissing('clanBossDungeon');
        $dungeon = $structure->clanBossDungeon;

        if ($dungeon === null || ! $dungeon->isClanBoss() || ! $dungeon->is_active) {
            throw new RuntimeException('Клан-босс здесь ещё не настроен.');
        }
        if ($structure->location_id === null) {
            throw new RuntimeException('У логова кланового босса не указана локация.');
        }

        return DB::transaction(function () use ($structure, $dungeon, $user): ClanBossRun {
            // Serializes run creation for this clan. Without locking a stable parent
            // row, two simultaneous requests could both pass the active-run check.
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedClanId = ClanMember::query()
                ->where('user_id', $lockedUser->id)
                ->value('clan_id');
            if ($lockedClanId === null) {
                throw new RuntimeException('Вы не состоите в клане.');
            }
            $clan = Clan::query()->whereKey($lockedClanId)->lockForUpdate()->firstOrFail();
            $membership = ClanMember::query()
                ->with('role')
                ->where('clan_id', $clan->id)
                ->where('user_id', $lockedUser->id)
                ->lockForUpdate()
                ->first();
            if ($membership === null) {
                throw new RuntimeException('Вы не состоите в клане.');
            }
            if (! $membership->role->hasPermission(ClanPermission::START_CLAN_BOSS) && (int) $clan->owner_id !== (int) $lockedUser->id) {
                throw new RuntimeException('У вас нет прав начинать бой с клан-боссом.');
            }

            if ((int) $lockedUser->location_id !== (int) $structure->location_id) {
                throw new RuntimeException('Открыть поход можно только у логова кланового босса.');
            }

            $lockedUser->loadMissing('player');
            if ($lockedUser->player === null || (int) $lockedUser->player->lvl < (int) $dungeon->min_level) {
                throw new RuntimeException(sprintf('Для похода нужен уровень %d.', $dungeon->min_level));
            }

            $exists = $this->findActiveRun($dungeon, (int) $clan->id);
            if ($exists !== null) {
                throw new RuntimeException('У клана уже открыт поход на этого босса.');
            }

            $stages = DungeonStage::query()
                ->where('dungeon_id', $dungeon->id)
                ->orderBy('number')
                ->get();
            $firstStage = $stages->first();
            if ($firstStage === null) {
                throw new RuntimeException('У данжа нет этапов-боссов.');
            }

            // A run contains the complete ordered boss chain. Check cooldowns for
            // every boss up front, not just stage one, so later-stage cooldowns
            // cannot be silently bypassed by starting a fresh run.
            foreach ($stages as $stage) {
                $this->assertClanCooldownPassed($stage, (int) $clan->id);
            }

            if ($dungeon->requiresKey()) {
                $key = $this->backpackService->getItem($lockedUser, $dungeon->entryItem);
                if ($key === null) {
                    throw new RuntimeException('Нет ключа для открытия похода.');
                }
                $this->backpackService->removeItemByShareItem($lockedUser, $dungeon->entryItem, 1);
            }

            $run = ClanBossRun::query()->create([
                'dungeon_id' => $dungeon->id,
                'clan_id' => $clan->id,
                'opened_by_user_id' => $lockedUser->id,
                'status' => ClanBossRun::STATUS_ACTIVE,
                'started_at' => now(),
                'expires_at' => $dungeon->time_limit_seconds !== null
                    ? now()->addSeconds($dungeon->time_limit_seconds)
                    : null,
            ]);

            $firstStageState = $this->spawnStage($run->fresh(), $firstStage);
            $run->update(['current_stage_id' => $firstStage->id]);
            $firstStageState->loadMissing('bossInstance.monster');
            $bossMonster = $firstStageState->bossInstance?->monster;
            ClanBossRunStarted::dispatch(
                clanId: (int) $clan->id,
                dungeonNameTranslations: $dungeon->getTranslations('name'),
                bossNameTranslations: $bossMonster?->getTranslations('name') ?? ['ru' => 'босс'],
            );

            return $run->fresh();
        });
    }

    /**
     * Проверки перед ударом. Возвращает залоченное состояние этапа.
     *
     * @throws RuntimeException
     */
    public function assertCanHit(User $user, MonsterOnLocation $boss): ClanBossRunStage
    {
        $stageState = ClanBossRunStage::query()
            ->whereKey($boss->clan_boss_stage_state_id)
            ->lockForUpdate()
            ->first();

        if ($stageState === null || ! $stageState->isActive()) {
            throw new RuntimeException('Этот босс уже повержен.');
        }

        $run = ClanBossRun::query()->whereKey($stageState->clan_boss_run_id)->lockForUpdate()->firstOrFail();
        if (! $run->isActive() || $run->isExpired()) {
            throw new RuntimeException('Поход завершён.');
        }

        $membership = $user->clanMembership;
        if ($membership === null || (int) $membership->clan_id !== (int) $run->clan_id) {
            throw new RuntimeException('Бить босса может только свой клан.');
        }

        $attackState = $this->lockPlayerAttackState($run, $user);
        if ($attackState->cooldown_until !== null && now()->lt($attackState->cooldown_until)) {
            throw new RuntimeException(sprintf(
                'Пакет ударов восстановится через %s.',
                $this->formatCompactDuration((int) now()->diffInSeconds($attackState->cooldown_until)),
            ));
        }

        $batchSize = max(1, (int) $run->dungeon->clan_boss_hit_batch_size);
        if ($attackState->hits_in_batch >= $batchSize && $attackState->cooldown_until === null) {
            // Recovery may have elapsed between scheduler ticks; the next hit
            // starts a fresh batch, without requiring a background reset job.
            $attackState->forceFill(['hits_in_batch' => 0])->save();
        }

        return $stageState;
    }

    public function recordHit(User $user, ClanBossRunStage $stageState, int $damage): void
    {
        $run = ClanBossRun::query()->with('dungeon')->whereKey($stageState->clan_boss_run_id)->firstOrFail();
        $attackState = $this->lockPlayerAttackState($run, $user);
        $batchSize = max(1, (int) $run->dungeon->clan_boss_hit_batch_size);
        $cooldownSeconds = max(0, (int) $run->dungeon->clan_boss_hit_batch_cooldown_seconds);

        if ($attackState->cooldown_until !== null && now()->greaterThanOrEqualTo($attackState->cooldown_until)) {
            $attackState->hits_in_batch = 0;
            $attackState->cooldown_until = null;
        }

        $attackState->hits_in_batch++;
        if ($attackState->hits_in_batch >= $batchSize) {
            $attackState->cooldown_until = $cooldownSeconds > 0 ? now()->addSeconds($cooldownSeconds) : null;
            if ($cooldownSeconds === 0) {
                $attackState->hits_in_batch = 0;
            }
        }
        $attackState->save();

        try {
            $contribution = ClanBossContribution::query()->firstOrCreate(
                [
                    'clan_boss_run_id' => $stageState->clan_boss_run_id,
                    'dungeon_stage_id' => $stageState->dungeon_stage_id,
                    'user_id' => $user->id,
                ],
                ['damage' => 0, 'hits' => 0],
            );
        } catch (QueryException) {
            // Параллельный первый удар: строка уже создана соседней транзакцией.
            $contribution = ClanBossContribution::query()
                ->where('clan_boss_run_id', $stageState->clan_boss_run_id)
                ->where('dungeon_stage_id', $stageState->dungeon_stage_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        if ($damage > 0) {
            $contribution->increment('damage', $damage);
        }
        $contribution->increment('hits');
        $contribution->update(['last_hit_at' => now()]);
    }

    /** @return array{hits_left: int, batch_size: int, cooldown_until: ?CarbonInterface} */
    public function playerAttackBatchStatus(ClanBossRun $run, User $user): array
    {
        $run->loadMissing('dungeon');
        $batchSize = max(1, (int) $run->dungeon->clan_boss_hit_batch_size);
        $state = ClanBossPlayerAttackState::query()
            ->where('clan_boss_run_id', $run->id)
            ->where('user_id', $user->id)
            ->first();
        $cooldownUntil = $state?->cooldown_until;

        if ($cooldownUntil !== null && now()->lt($cooldownUntil)) {
            return ['hits_left' => 0, 'batch_size' => $batchSize, 'cooldown_until' => $cooldownUntil];
        }

        $hitsLeft = $state === null || ($cooldownUntil !== null && now()->greaterThanOrEqualTo($cooldownUntil))
            ? $batchSize
            : max(0, $batchSize - (int) $state->hits_in_batch);

        return ['hits_left' => $hitsLeft, 'batch_size' => $batchSize, 'cooldown_until' => null];
    }

    /**
     * Сколько ударов по этому боссу осталось в текущем пакете у игрока.
     * Возвращает null, если инстанс не относится к клановому боссу.
     */
    public function hitsLeftFor(MonsterOnLocation $boss, User $user): ?int
    {
        if (! $boss->isClanBoss()) {
            return null;
        }

        $boss->loadMissing('clanBossStageState.run.dungeon');
        $run = $boss->clanBossStageState?->run;
        if ($run === null) {
            return null;
        }

        return $this->playerAttackBatchStatus($run, $user)['hits_left'];
    }

    /**
     * Удобный вариант hitsLeftFor() для вызова из любого места, где под рукой
     * есть BattleDetail (например, атакуемый моб боя), а не сам инстанс босса.
     */
    public function hitsLeftForBattleDetail(?BattleDetail $randomAttackedMonster, User $user): ?int
    {
        $boss = $randomAttackedMonster?->locationMonster;
        if ($boss === null) {
            return null;
        }

        return $this->hitsLeftFor($boss, $user);
    }

    private function lockPlayerAttackState(ClanBossRun $run, User $user): ClanBossPlayerAttackState
    {
        ClanBossPlayerAttackState::query()->firstOrCreate(
            ['clan_boss_run_id' => $run->id, 'user_id' => $user->id],
            ['hits_in_batch' => 0],
        );

        return ClanBossPlayerAttackState::query()
            ->where('clan_boss_run_id', $run->id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Убийство общего инстанса. Идемпотентно: повторные вызовы (добивания
     * из соседних боев) тихо завершаются без двойных наград.
     */
    public function handleStageKill(MonsterOnLocation $boss, Player $killer, ?int $battleId, AttackResultDTO $log): void
    {
        $boss = MonsterOnLocation::query()->whereKey($boss->id)->lockForUpdate()->firstOrFail();
        $stageState = ClanBossRunStage::query()
            ->whereKey($boss->clan_boss_stage_state_id)
            ->lockForUpdate()
            ->firstOrFail();

        if (! $stageState->isActive()) {
            return;
        }

        $run = ClanBossRun::query()->whereKey($stageState->clan_boss_run_id)->lockForUpdate()->firstOrFail();
        if (! $run->isActive()) {
            return;
        }

        $boss->active = 0;
        $boss->save();

        $stageState->update([
            'status' => ClanBossRunStage::STATUS_KILLED,
            'killed_at' => now(),
        ]);

        $monster = $boss->monster;
        $killer->loadMissing('user');

        BossDefeatLog::create([
            'battle_id' => $battleId,
            'monster_id' => $monster->id,
            'player_id' => $killer->id,
            'turns_taken' => 0,
            'mechanics_triggered' => [],
            'final_phase' => $boss->current_phase ?? 1,
        ]);

        $summary = $this->rewardService->grantForStage($run, $stageState, $monster, $killer->user);
        ClanBossDefeated::dispatch(
            clanId: (int) $run->clan_id,
            bossNameTranslations: $monster->getTranslations('name'),
        );

        if ($stageState->stage->clan_cooldown_seconds !== null) {
            ClanBossCooldown::query()->updateOrCreate(
                [
                    'dungeon_stage_id' => $stageState->dungeon_stage_id,
                    'clan_id' => $run->clan_id,
                ],
                ['available_at' => now()->addSeconds($stageState->stage->clan_cooldown_seconds)],
            );
        }

        $log->log(sprintf(
            '<p><b class="color-legendary">🏆 Клан-босс %s повержен! Награду получили: %d.</b></p>',
            e($monster->name),
            $summary['qualified'],
        ));

        $this->advanceToNextStage($run, $stageState, $log);
    }

    /**
     * @return list<array{user: ?User, damage: int, hits: int, rewarded: bool}>
     */
    public function ratingForStage(int $runId, int $stageId, int $limit = 20): array
    {
        return ClanBossContribution::query()
            ->with('user')
            ->where('clan_boss_run_id', $runId)
            ->where('dungeon_stage_id', $stageId)
            ->orderByDesc('damage')
            ->limit($limit)
            ->get()
            ->map(fn (ClanBossContribution $row): array => [
                'user' => $row->user,
                'damage' => (int) $row->damage,
                'hits' => (int) $row->hits,
                'rewarded' => (bool) $row->rewarded,
            ])
            ->all();
    }

    /** @return list<array{user: ?User, damage: int, hits: int, rewarded: bool}> */
    public function ratingForRun(int $runId): array
    {
        return ClanBossContribution::query()
            ->with(['user.player', 'user.clanMembership.clan'])
            ->selectRaw('user_id, SUM(damage) AS total_damage, SUM(hits) AS total_hits, MAX(rewarded) AS received_reward')
            ->where('clan_boss_run_id', $runId)
            ->groupBy('user_id')
            ->orderByDesc('total_damage')
            ->orderBy('user_id')
            ->get()
            ->map(fn (ClanBossContribution $row): array => [
                'user' => $row->user,
                'damage' => (int) $row->total_damage,
                'hits' => (int) $row->total_hits,
                'rewarded' => (bool) $row->received_reward,
            ])
            ->all();
    }

    public function myContribution(int $runId, int $stageId, int $userId): ?ClanBossContribution
    {
        return ClanBossContribution::query()
            ->where('clan_boss_run_id', $runId)
            ->where('dungeon_stage_id', $stageId)
            ->where('user_id', $userId)
            ->first();
    }

    public function expireOverdue(CarbonInterface $now): int
    {
        $processed = 0;

        ClanBossRun::query()
            ->where('status', ClanBossRun::STATUS_ACTIVE)
            ->where('expires_at', '<=', $now)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(function (ClanBossRun $run) use (&$processed): void {
                DB::transaction(function () use ($run): void {
                    $this->expireRun($run);
                });
                $processed++;
            });

        return $processed;
    }

    private function expireRun(ClanBossRun $run): void
    {
        $locked = ClanBossRun::query()->whereKey($run->id)->lockForUpdate()->firstOrFail();
        if (! $locked->isActive()) {
            return;
        }

        $current = ClanBossRunStage::query()
            ->where('clan_boss_run_id', $locked->id)
            ->where('status', ClanBossRunStage::STATUS_ACTIVE)
            ->lockForUpdate()
            ->get();

        foreach ($current as $stageState) {
            $stageState->update(['status' => ClanBossRunStage::STATUS_EXPIRED]);
            $this->deactivateInstance($stageState);
            $this->rewardService->grantGoldForUnfinishedStage($locked, $stageState);
        }

        $locked->update([
            'status' => ClanBossRun::STATUS_EXPIRED,
            'finished_at' => now(),
        ]);
        $locked->loadMissing('dungeon');
        ClanBossRunExpired::dispatch(
            clanId: (int) $locked->clan_id,
            dungeonNameTranslations: $locked->dungeon->getTranslations('name'),
        );
    }

    private function advanceToNextStage(ClanBossRun $run, ClanBossRunStage $doneState, AttackResultDTO $log): void
    {
        $run = ClanBossRun::query()->whereKey($run->id)->lockForUpdate()->firstOrFail();
        if (! $run->isActive()) {
            return;
        }

        if ($run->isExpired()) {
            $this->expireRun($run);

            return;
        }

        $doneStage = $doneState->stage;
        $next = $doneStage->next_stage_id !== null
            ? DungeonStage::query()->find($doneStage->next_stage_id)
            : DungeonStage::query()
                ->where('dungeon_id', $run->dungeon_id)
                ->where('number', '>', $doneStage->number)
                ->orderBy('number')
                ->first();

        if ($next === null || (int) $next->dungeon_id !== (int) $run->dungeon_id) {
            $run->update([
                'status' => ClanBossRun::STATUS_COMPLETED,
                'finished_at' => now(),
            ]);
            $log->log('<p><b>Поход завершён: все боссы повержены!</b></p>');

            return;
        }

        $this->spawnStage($run, $next);
        $run->update(['current_stage_id' => $next->id]);
        $log->log(sprintf(
            '<p><b>Следующий босс пробудился: %s!</b></p>',
            e((string) $next->name),
        ));
    }

    private function spawnStage(ClanBossRun $run, DungeonStage $stage): ClanBossRunStage
    {
        $definition = $stage->monsters()->with('monster')->first();
        if ($definition === null || $definition->monster === null) {
            throw new RuntimeException(sprintf('У этапа %d нет босса.', $stage->number));
        }

        $structure = Structure::query()
            ->where('dungeon_id', $run->dungeon_id)
            ->where('type', Structure::TYPE_CLAN_BOSS)
            ->orderBy('id')
            ->first();

        if ($structure === null || $structure->location_id === null) {
            throw new RuntimeException('Нет структуры логова для этого данжа.');
        }

        $monster = $definition->monster;

        $stageState = ClanBossRunStage::query()->create([
            'clan_boss_run_id' => $run->id,
            'dungeon_stage_id' => $stage->id,
            'hp_max' => $monster->hp,
            'status' => ClanBossRunStage::STATUS_ACTIVE,
            'started_at' => now(),
            // Клановые боссы живут до убийства или завершения всего похода.
            'expires_at' => null,
        ]);

        $instance = MonsterOnLocation::query()->create([
            'location_id' => $structure->location_id,
            'monster_id' => $monster->id,
            'hp_now' => $monster->hp,
            'hp_max' => $monster->hp,
            'active' => 1,
            'aggression' => 0,
            'is_drop_money' => 1,
            'current_phase' => 1,
            'clan_boss_stage_state_id' => $stageState->id,
        ]);

        $stageState->update(['monster_on_location_id' => $instance->id]);

        return $stageState->fresh();
    }

    private function deactivateInstance(ClanBossRunStage $stageState): void
    {
        if ($stageState->monster_on_location_id === null) {
            return;
        }

        MonsterOnLocation::query()
            ->whereKey($stageState->monster_on_location_id)
            ->update(['active' => 0]);
    }

    private function assertClanCooldownPassed(DungeonStage $stage, int $clanId): void
    {
        $cooldown = ClanBossCooldown::query()
            ->where('dungeon_stage_id', $stage->id)
            ->where('clan_id', $clanId)
            ->first();

        if ($cooldown !== null && $cooldown->available_at !== null && now()->lt($cooldown->available_at)) {
            throw new RuntimeException(sprintf(
                'Босс будет доступен через %d сек.',
                now()->diffInSeconds($cooldown->available_at),
            ));
        }
    }

    private function formatCompactDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        if ($seconds < 60) {
            return $seconds.'с';
        }

        // For longer waits, round up to a full minute so the displayed time
        // never understates the remaining cooldown.
        $seconds = intdiv($seconds + 59, 60) * 60;
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days.'д';
        }
        if ($hours > 0) {
            $parts[] = $hours.'ч';
        }
        if ($minutes > 0) {
            $parts[] = $minutes.'м';
        }

        return $parts !== [] ? implode(' ', $parts) : '0м';
    }
}
