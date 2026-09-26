<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\UseCases;

use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;

final class GetActiveWorldEventWidget
{
    /**
     * @return list<array{
     *     run_id: int,
     *     title: string,
     *     stage_title: string,
     *     stage_position: int,
     *     objective_type: string,
     *     progress_label: string,
     *     collected_count: int,
     *     global_limit: int,
     *     remaining_seconds: int
     * }>
     */
    public function execute(): array
    {
        return WorldEventRun::query()
            ->with(['event:id,title', 'currentStage:id,title,position,objective_type,global_limit'])
            ->where('status', WorldEventRun::STATUS_ACTIVE)
            ->where('ends_at', '>', now())
            ->orderBy('ends_at')
            ->get()
            ->map(static function (WorldEventRun $run): array {
                $stage = $run->currentStage;
                $objectiveType = $stage->objective_type;

                return [
                    'run_id' => (int) $run->id,
                    'title' => $run->event->title,
                    'stage_title' => $stage->title,
                    'stage_position' => (int) $stage->position,
                    'objective_type' => $objectiveType->value,
                    'progress_label' => $objectiveType === WorldEventObjectiveType::KILL
                        ? 'Убито монстров'
                        : 'Собрано ресурсов',
                    'collected_count' => (int) $run->collected_count,
                    'global_limit' => (int) $stage->global_limit,
                    'remaining_seconds' => max(0, $run->ends_at->timestamp - now()->timestamp),
                ];
            })
            ->values()
            ->all();
    }
}
