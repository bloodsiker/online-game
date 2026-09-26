<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Observers;

use App\Modules\Event\Domain\Events\WorldEventFinished;
use App\Modules\Event\Domain\Events\WorldEventStateChanged;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;

final class WorldEventRunObserver
{
    public function created(WorldEventRun $run): void
    {
        WorldEventStateChanged::dispatch((int) $run->id);
    }

    public function updated(WorldEventRun $run): void
    {
        if ($run->wasChanged(['status', 'current_stage_id', 'collected_count'])) {
            WorldEventStateChanged::dispatch((int) $run->id);
        }

        if (! $run->wasChanged('status')
            || $run->getOriginal('status') !== WorldEventRun::STATUS_ACTIVE
            || $run->status === WorldEventRun::STATUS_ACTIVE) {
            return;
        }

        $worldEvent = $run->event()->first(['id', 'title']);
        if ($worldEvent === null) {
            return;
        }

        WorldEventFinished::dispatch(
            eventId: (int) $worldEvent->id,
            runId: (int) $run->id,
            title: $worldEvent->title,
        );
    }
}
