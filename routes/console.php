<?php

use App\Modules\Scheduler\Application\Services\ScheduledTaskManager;
use App\Modules\Scheduler\Application\Services\ScheduledTaskRunner;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$taskManager = app(ScheduledTaskManager::class);

foreach ($taskManager->definitions() as $definition) {
    $event = Schedule::call(
        static fn (): string => app(ScheduledTaskRunner::class)->run($definition->key),
    );

    $taskManager->applySchedule($event, $definition)
        ->name('scheduled-task:'.$definition->key)
        ->when(static fn (): bool => app(ScheduledTaskManager::class)->isEnabled($definition->key))
        ->withoutOverlapping(max(1, (int) ceil($definition->lockSeconds / 60)));
}
