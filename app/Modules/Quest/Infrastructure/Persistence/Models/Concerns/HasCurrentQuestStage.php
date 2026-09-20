<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models\Concerns;

trait HasCurrentQuestStage
{
    public function isCurrentStageComplete(): bool
    {
        $objectives = $this->current_stage_id !== null
            ? $this->objectives->filter(fn ($objective) => $objective->questObjective->stage_id === $this->current_stage_id)
            : $this->objectives;

        if ($this->currentStage?->isWaiting() && ! $this->isCurrentStageReady()) {
            return false;
        }

        if ($objectives->isEmpty()) {
            return (bool) $this->currentStage?->isWaiting();
        }

        return $objectives->every(fn ($objective) => $objective->questObjective->type === 'deliver'
            || $objective->amount >= $objective->questObjective->required_amount
        );
    }

    public function isCurrentStageReady(): bool
    {
        if (! $this->currentStage?->isWaiting()) {
            return true;
        }

        return $this->current_stage_ready_at !== null
            && now()->greaterThanOrEqualTo($this->current_stage_ready_at);
    }
}
