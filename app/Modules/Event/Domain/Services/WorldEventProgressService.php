<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Services;

use App\Modules\Event\Application\DTOs\WorldEventCollectionResult;
use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventPlayerProgress;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;
use App\Modules\Influence\Domain\Services\MapInfluenceService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

final class WorldEventProgressService
{
    public function __construct(
        private readonly MapInfluenceService $influenceService,
        private readonly WorldEventLifecycleService $lifecycleService,
    ) {}

    public function advance(
        User $user,
        int $runId,
        WorldEventObjectiveType $objectiveType,
        int $targetId,
        int $amount = 1,
    ): WorldEventCollectionResult {
        return DB::transaction(fn (): WorldEventCollectionResult => $this->advanceLocked(
            user: $user,
            runId: $runId,
            objectiveType: $objectiveType,
            targetId: $targetId,
            amount: $amount,
        ));
    }

    private function advanceLocked(
        User $user,
        int $runId,
        WorldEventObjectiveType $objectiveType,
        int $targetId,
        int $amount,
    ): WorldEventCollectionResult {
        $run = WorldEventRun::query()->with(['event', 'currentStage.targets'])->lockForUpdate()->find($runId);
        if ($run === null
            || $run->currentStage === null
            || $run->status !== WorldEventRun::STATUS_ACTIVE
            || $run->ends_at->isPast()) {
            return new WorldEventCollectionResult(false, 'Событие уже завершено.');
        }

        $event = $run->event;
        $stage = $run->currentStage;
        $targetBelongsToStage = $stage->targets->contains(
            fn ($target): bool => $objectiveType === WorldEventObjectiveType::COLLECT
                ? (int) $target->share_item_id === $targetId
                : (int) $target->monster_id === $targetId,
        );
        if ($stage->objective_type !== $objectiveType || ! $targetBelongsToStage) {
            return new WorldEventCollectionResult(false, 'Эта цель не относится к текущему этапу события.');
        }

        if ((int) $run->collected_count >= (int) $stage->global_limit) {
            return new WorldEventCollectionResult(false, 'Общий лимит этапа уже исчерпан.');
        }

        $progress = WorldEventPlayerProgress::query()
            ->where('world_event_run_id', $run->id)
            ->where('world_event_stage_id', $stage->id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();
        $progress ??= WorldEventPlayerProgress::query()->create([
            'world_event_run_id' => $run->id,
            'world_event_stage_id' => $stage->id,
            'user_id' => $user->id,
        ]);

        if ((int) $progress->collected_count >= (int) $stage->player_limit) {
            return new WorldEventCollectionResult(false, 'Вы уже достигли личного лимита этого этапа.');
        }

        $available = min(
            (int) $stage->player_limit - (int) $progress->collected_count,
            (int) $stage->global_limit - (int) $run->collected_count,
        );
        if ($amount < 1 || $amount > $available) {
            return new WorldEventCollectionResult(false, 'Эта цель превысит доступный вам лимит этапа.');
        }

        $influence = $amount * (int) $stage->influence_per_item;
        $run->collected_count += $amount;
        $run->next_spawn_at ??= now()->addSeconds((int) $stage->respawn_seconds);

        $progress->collected_count += $amount;
        $progress->influence_earned += $influence;
        $progress->save();

        $stageAdvanced = false;
        $nextStage = null;
        if ((int) $run->collected_count >= (int) $stage->global_limit) {
            $nextStage = $event->stages()
                ->where('position', '>', $stage->position)
                ->first();
            if ($nextStage === null) {
                $finishedAt = now();
                $run->status = WorldEventRun::STATUS_COMPLETED;
                $run->finished_at = $finishedAt;
                $run->next_spawn_at = null;
                $event->scheduleNextStartAfter($finishedAt);
                $event->save();
            } else {
                $run->current_stage_id = $nextStage->id;
                $run->collected_count = 0;
                $run->next_spawn_at = null;
                $stageAdvanced = true;
            }
        }
        $run->save();

        if ($stageAdvanced) {
            DB::afterCommit(fn () => $this->lifecycleService->activateCurrentStage((int) $run->id));
        }

        $influenceResult = $this->influenceService->award(
            user: $user,
            map: $event->influenceMapId(),
            amount: $influence,
            sourceType: 'world_event',
            sourceId: (int) $run->id,
            idempotencyKey: sprintf('world-event:%d:stage:%d:user:%d:progress:%d', $run->id, $stage->id, $user->id, $progress->collected_count),
            description: sprintf('Событие «%s», этап «%s»', $event->title, $stage->title),
        );

        return new WorldEventCollectionResult(
            allowed: true,
            playerProgress: (int) $progress->collected_count,
            playerLimit: (int) $stage->player_limit,
            influenceAwarded: $influence,
            mapInfluence: $influenceResult->total,
            targetExpiresAt: $objectiveType === WorldEventObjectiveType::COLLECT
                ? $run->ends_at->copy()->addMinutes((int) $stage->item_lifetime_minutes)
                : null,
            stageAdvanced: $stageAdvanced,
            nextStageTitle: $nextStage?->title,
        );
    }
}
