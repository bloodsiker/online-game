<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\Services;

use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;

class QuestStageRuntimeService
{
    /** @return array{current_stage_id: ?int, current_stage_started_at: mixed, current_stage_ready_at: mixed} */
    public function stateFor(?QuestStage $stage): array
    {
        if ($stage === null) {
            return [
                'current_stage_id' => null,
                'current_stage_started_at' => null,
                'current_stage_ready_at' => null,
            ];
        }

        $startedAt = now();

        return [
            'current_stage_id' => $stage->id,
            'current_stage_started_at' => $startedAt,
            'current_stage_ready_at' => $stage->isWaiting()
                ? $startedAt->copy()->addSeconds(max(1, (int) $stage->wait_duration_seconds))
                : null,
        ];
    }

    public function messageFor(QuestPlayer|QuestClanProgress|null $progress): ?string
    {
        if ($progress === null || ! $progress->currentStage?->isWaiting()) {
            return null;
        }

        if (! $progress->isCurrentStageReady()) {
            return $progress->currentStage->waiting_text ?: 'Ты пришёл слишком рано. Возвращайся позже.';
        }

        return $progress->currentStage->ready_text ?: null;
    }
}
