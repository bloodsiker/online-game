<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Application\Services;

use App\Modules\Scheduler\Domain\DTOs\ScheduledTaskDefinition;
use App\Modules\Scheduler\Domain\Enums\ScheduledTaskFrequency;
use App\Modules\Scheduler\Infrastructure\Persistence\Models\ScheduledTaskSetting;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class ScheduledTaskManager
{
    private const CACHE_SECONDS = 15;

    public function __construct(private readonly ScheduledTaskRegistry $registry) {}

    /** @return array<string, ScheduledTaskDefinition> */
    public function definitions(): array
    {
        return $this->registry->all();
    }

    public function ensureSettings(): void
    {
        foreach ($this->definitions() as $definition) {
            ScheduledTaskSetting::query()->firstOrCreate(
                ['task_key' => $definition->key],
                [
                    'enabled' => true,
                    'frequency' => $definition->defaultFrequency,
                ],
            );
        }
    }

    public function setting(ScheduledTaskDefinition $definition): ScheduledTaskSetting
    {
        try {
            if (! Schema::hasTable('scheduled_task_settings')) {
                return $this->defaultSetting($definition);
            }

            return ScheduledTaskSetting::query()->firstOrCreate(
                ['task_key' => $definition->key],
                [
                    'enabled' => true,
                    'frequency' => $definition->defaultFrequency,
                ],
            );
        } catch (Throwable) {
            return $this->defaultSetting($definition);
        }
    }

    public function applySchedule(Event $event, ScheduledTaskDefinition $definition): Event
    {
        $frequency = $this->setting($definition)->frequency;
        if (! $definition->allows($frequency)) {
            $frequency = $definition->defaultFrequency;
        }

        return $frequency->apply($event);
    }

    public function isEnabled(string $key): bool
    {
        $definition = $this->registry->find($key);
        if (! $definition->settingsMutable) {
            return true;
        }

        return Cache::remember(
            $this->cacheKey($key),
            self::CACHE_SECONDS,
            fn (): bool => $this->setting($definition)->enabled,
        );
    }

    public function update(string $key, bool $enabled, string $frequency, ?int $administratorId): void
    {
        $definition = $this->registry->find($key);
        if (! $definition->settingsMutable) {
            throw ValidationException::withMessages([
                'task' => 'Настройки этой критической задачи защищены.',
            ]);
        }

        $frequencyEnum = ScheduledTaskFrequency::tryFrom($frequency);
        if ($frequencyEnum === null || ! $definition->allows($frequencyEnum)) {
            throw ValidationException::withMessages([
                'frequency' => 'Выбран недоступный интервал запуска.',
            ]);
        }

        ScheduledTaskSetting::query()->updateOrCreate(
            ['task_key' => $key],
            [
                'enabled' => $enabled,
                'frequency' => $frequencyEnum,
                'updated_by' => $administratorId,
            ],
        );
        Cache::forget($this->cacheKey($key));
    }

    private function defaultSetting(ScheduledTaskDefinition $definition): ScheduledTaskSetting
    {
        $setting = new ScheduledTaskSetting;
        $setting->task_key = $definition->key;
        $setting->enabled = true;
        $setting->frequency = $definition->defaultFrequency;

        return $setting;
    }

    private function cacheKey(string $key): string
    {
        return 'scheduled-task:enabled:'.$key;
    }
}
