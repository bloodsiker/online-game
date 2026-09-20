<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Application\Services;

use App\Modules\Scheduler\Infrastructure\Persistence\Models\ScheduledTaskRun;
use App\Modules\Scheduler\Infrastructure\Persistence\Models\ScheduledTaskSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ScheduledTaskRunner
{
    public function __construct(private readonly ScheduledTaskRegistry $registry) {}

    public function run(
        string $key,
        string $trigger = ScheduledTaskRun::TRIGGER_SCHEDULER,
        ?int $administratorId = null,
    ): string {
        $definition = $this->registry->find($key);
        $lock = Cache::lock('scheduled-task:running:'.$key, $definition->lockSeconds);

        if (! $lock->get()) {
            throw new RuntimeException('Задача уже выполняется. Дождитесь её завершения.');
        }

        $startedAt = now();
        $startedNs = hrtime(true);
        $this->markRunning($key, $startedAt);

        try {
            $output = Str::limit($this->registry->execute($key), 60000, '…');
            $finishedAt = now();
            $durationMs = (int) round((hrtime(true) - $startedNs) / 1_000_000);
            $this->markFinished($key, ScheduledTaskRun::STATUS_SUCCESS, $finishedAt, $durationMs, $output, null);

            if ($trigger === ScheduledTaskRun::TRIGGER_MANUAL
                || Cache::add('scheduled-task:history:'.$key, true, $definition->successHistoryIntervalSeconds)) {
                $this->writeHistory(
                    key: $key,
                    status: ScheduledTaskRun::STATUS_SUCCESS,
                    trigger: $trigger,
                    administratorId: $administratorId,
                    startedAt: $startedAt,
                    finishedAt: $finishedAt,
                    durationMs: $durationMs,
                    output: $output,
                );
            }

            return $output;
        } catch (Throwable $exception) {
            $finishedAt = now();
            $durationMs = (int) round((hrtime(true) - $startedNs) / 1_000_000);
            $error = Str::limit($exception->getMessage(), 60000, '…');
            $this->markFinished($key, ScheduledTaskRun::STATUS_FAILED, $finishedAt, $durationMs, null, $error);
            $this->writeHistory(
                key: $key,
                status: ScheduledTaskRun::STATUS_FAILED,
                trigger: $trigger,
                administratorId: $administratorId,
                startedAt: $startedAt,
                finishedAt: $finishedAt,
                durationMs: $durationMs,
                error: $error,
            );

            throw $exception;
        } finally {
            $lock->release();
        }
    }

    private function markRunning(string $key, $startedAt): void
    {
        if (! $this->hasTables()) {
            return;
        }

        ScheduledTaskSetting::query()->where('task_key', $key)->update([
            'last_status' => 'running',
            'last_started_at' => $startedAt,
            'last_finished_at' => null,
            'last_duration_ms' => null,
            'last_output' => null,
            'last_error' => null,
        ]);
    }

    private function markFinished(
        string $key,
        string $status,
        $finishedAt,
        int $durationMs,
        ?string $output,
        ?string $error,
    ): void {
        if (! $this->hasTables()) {
            return;
        }

        ScheduledTaskSetting::query()->where('task_key', $key)->update([
            'last_status' => $status,
            'last_finished_at' => $finishedAt,
            'last_duration_ms' => $durationMs,
            'last_output' => $output,
            'last_error' => $error,
        ]);
    }

    private function writeHistory(
        string $key,
        string $status,
        string $trigger,
        ?int $administratorId,
        $startedAt,
        $finishedAt,
        int $durationMs,
        ?string $output = null,
        ?string $error = null,
    ): void {
        if (! $this->hasTables()) {
            return;
        }

        ScheduledTaskRun::query()->create([
            'task_key' => $key,
            'status' => $status,
            'trigger' => $trigger,
            'initiated_by' => $administratorId,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'duration_ms' => $durationMs,
            'output' => $output,
            'error' => $error,
        ]);

        ScheduledTaskRun::query()->where('created_at', '<', now()->subDays(30))->delete();
    }

    private function hasTables(): bool
    {
        try {
            return Schema::hasTable('scheduled_task_settings') && Schema::hasTable('scheduled_task_runs');
        } catch (Throwable) {
            return false;
        }
    }
}
