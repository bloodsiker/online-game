<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Application\DTOs\ClanBossPageDTO;
use App\Modules\Dungeon\Application\Services\ClanBossService;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossContribution;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class GetClanBossPage
{
    public function __construct(
        private readonly ResolveClanContext $resolveClanContext,
        private readonly ClanBossService $clanBossService,
    ) {}

    public function execute(User $user, ?int $archiveRunId = null): ClanBossPageDTO
    {
        $context = $this->resolveClanContext->require($user);
        $clan = $context->clan;

        $run = $this->clanBossService->findActiveRunForClan((int) $clan->id);

        $currentState = null;
        $previousBosses = collect();
        $nextBoss = null;
        $upcomingBosses = collect();
        $damageRows = collect();
        $myContribution = null;
        $hitsLeft = null;
        $personalAvailableAt = null;

        $archiveRuns = ClanBossRun::query()
            ->with('dungeon')
            ->where('clan_id', $clan->id)
            ->whereIn('status', [ClanBossRun::STATUS_COMPLETED, ClanBossRun::STATUS_EXPIRED])
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'archive_page')
            ->withQueryString();

        $archivedRun = null;
        $archivedStages = collect();
        $archivedDamageRows = collect();
        $archivedStageDamage = collect();

        if ($archiveRunId !== null) {
            $archivedRun = ClanBossRun::query()
                ->with('dungeon')
                ->where('clan_id', $clan->id)
                ->whereIn('status', [ClanBossRun::STATUS_COMPLETED, ClanBossRun::STATUS_EXPIRED])
                ->findOrFail($archiveRunId);
        } elseif ($run === null) {
            // Show the most recent result by default when no attempt is active.
            $archivedRun = $archiveRuns->first();
        }

        if ($archivedRun !== null) {
            $archivedStages = ClanBossRunStage::query()
                ->with(['stage', 'bossInstance.monster'])
                ->where('clan_boss_run_id', $archivedRun->id)
                ->orderBy('id')
                ->get();

            $archivedDamageRows = $this->damageRows($archivedRun->id);
            $archivedStageDamage = ClanBossContribution::query()
                ->selectRaw('dungeon_stage_id, SUM(damage) AS total_damage')
                ->where('clan_boss_run_id', $archivedRun->id)
                ->groupBy('dungeon_stage_id')
                ->pluck('total_damage', 'dungeon_stage_id');
        }

        if ($run !== null) {
            $previousBosses = ClanBossRunStage::query()
                ->with(['stage', 'bossInstance.monster'])
                ->where('clan_boss_run_id', $run->id)
                ->where('status', ClanBossRunStage::STATUS_KILLED)
                ->orderBy('id')
                ->get();

            $currentState = ClanBossRunStage::query()
                ->with(['stage', 'bossInstance.monster'])
                ->where('clan_boss_run_id', $run->id)
                ->where('dungeon_stage_id', $run->current_stage_id)
                ->first();

            if ($currentState?->stage !== null) {
                $currentStage = $currentState->stage;
                $nextStage = $currentStage->next_stage_id !== null
                    ? DungeonStage::query()->find($currentStage->next_stage_id)
                    : DungeonStage::query()
                        ->where('dungeon_id', $run->dungeon_id)
                        ->where('number', '>', $currentStage->number)
                        ->orderBy('number')
                        ->first();

                $visitedStageIds = [(int) $currentStage->id => true];
                $stageCursor = $currentStage;
                while ($nextStage !== null
                    && (int) $nextStage->dungeon_id === (int) $run->dungeon_id
                    && ! isset($visitedStageIds[(int) $nextStage->id])
                    && $upcomingBosses->count() < 100) {
                    $visitedStageIds[(int) $nextStage->id] = true;
                    $bossDefinition = $nextStage->monsters()->with(['monster', 'stage'])->first();
                    if ($bossDefinition === null || $bossDefinition->monster === null) {
                        break;
                    }

                    $upcomingBosses->push($bossDefinition);
                    $stageCursor = $nextStage;
                    $nextStage = $stageCursor->next_stage_id !== null
                        ? DungeonStage::query()->find($stageCursor->next_stage_id)
                        : DungeonStage::query()
                            ->where('dungeon_id', $run->dungeon_id)
                            ->where('number', '>', $stageCursor->number)
                            ->orderBy('number')
                            ->first();
                }

                $nextBoss = $upcomingBosses->first();
            }

            $damageRows = $this->damageRows($run->id);

            if ($currentState !== null) {
                $myContribution = $this->clanBossService->myContribution(
                    $run->id,
                    (int) $currentState->dungeon_stage_id,
                    $user->id,
                );

                $batchStatus = $this->clanBossService->playerAttackBatchStatus($run, $user);
                $hitsLeft = $batchStatus['hits_left'];
                $personalAvailableAt = $batchStatus['cooldown_until'];
            }
        }

        return new ClanBossPageDTO(
            clan: $clan,
            membership: $context->membership,
            run: $run,
            currentState: $currentState,
            previousBosses: $previousBosses,
            nextBoss: $nextBoss,
            upcomingBosses: $upcomingBosses,
            damageRows: $damageRows,
            myContribution: $myContribution,
            hitsLeft: $hitsLeft,
            personalAvailableAt: $personalAvailableAt,
            archiveRuns: $archiveRuns,
            archivedRun: $archivedRun,
            archivedStages: $archivedStages,
            archivedDamageRows: $archivedDamageRows,
            archivedStageDamage: $archivedStageDamage,
        );
    }

    /** @return Collection<int, array{user: ?User, damage: int, hits: int, rewarded: bool}> */
    private function damageRows(int $runId): Collection
    {
        return ClanBossContribution::query()
            ->with('user')
            ->selectRaw('user_id, SUM(damage) AS total_damage, SUM(hits) AS total_hits, MAX(rewarded) AS received_reward')
            ->where('clan_boss_run_id', $runId)
            ->groupBy('user_id')
            ->orderByDesc('total_damage')
            ->get()
            ->map(fn (ClanBossContribution $row): array => [
                'user' => $row->user,
                'damage' => (int) $row->total_damage,
                'hits' => (int) $row->total_hits,
                'rewarded' => (bool) $row->received_reward,
            ]);
    }
}
