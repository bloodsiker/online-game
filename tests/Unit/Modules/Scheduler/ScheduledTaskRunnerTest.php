<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Scheduler;

use App\Modules\Scheduler\Application\Services\ScheduledTaskManager;
use App\Modules\Scheduler\Application\Services\ScheduledTaskRegistry;
use App\Modules\Scheduler\Application\Services\ScheduledTaskRunner;
use App\Modules\Scheduler\Domain\DTOs\ScheduledTaskDefinition;
use App\Modules\Scheduler\Domain\Enums\ScheduledTaskFrequency;
use App\Modules\Scheduler\Infrastructure\Persistence\Models\ScheduledTaskRun;
use App\Modules\Scheduler\Infrastructure\Persistence\Models\ScheduledTaskSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ScheduledTaskRunnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('cache.default', 'array');
        DB::purge('sqlite');

        Schema::create('scheduled_task_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('task_key')->unique();
            $table->boolean('enabled')->default(true);
            $table->string('frequency');
            $table->string('last_status')->nullable();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('last_finished_at')->nullable();
            $table->unsignedBigInteger('last_duration_ms')->nullable();
            $table->text('last_output')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('scheduled_task_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('task_key');
            $table->string('status');
            $table->string('trigger');
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function test_manual_run_updates_current_state_and_writes_history(): void
    {
        $definition = new ScheduledTaskDefinition(
            key: 'test.task',
            name: 'Тест',
            description: 'Тестовая задача',
            defaultFrequency: ScheduledTaskFrequency::EVERY_MINUTE,
            allowedFrequencies: [ScheduledTaskFrequency::EVERY_MINUTE],
        );
        ScheduledTaskSetting::query()->create([
            'task_key' => $definition->key,
            'enabled' => true,
            'frequency' => $definition->defaultFrequency,
        ]);

        $registry = Mockery::mock(ScheduledTaskRegistry::class);
        $registry->shouldReceive('find')->once()->with('test.task')->andReturn($definition);
        $registry->shouldReceive('execute')->once()->with('test.task')->andReturn('Готово.');

        $result = (new ScheduledTaskRunner($registry))->run(
            'test.task',
            ScheduledTaskRun::TRIGGER_MANUAL,
        );

        $this->assertSame('Готово.', $result);
        $this->assertSame(ScheduledTaskRun::STATUS_SUCCESS, ScheduledTaskSetting::query()->firstOrFail()->last_status);
        $this->assertDatabaseHas('scheduled_task_runs', [
            'task_key' => 'test.task',
            'status' => ScheduledTaskRun::STATUS_SUCCESS,
            'trigger' => ScheduledTaskRun::TRIGGER_MANUAL,
            'output' => 'Готово.',
        ]);
    }

    public function test_failed_run_is_always_written_to_history(): void
    {
        $definition = new ScheduledTaskDefinition(
            key: 'test.failed',
            name: 'Ошибка',
            description: 'Тестовая задача с ошибкой',
            defaultFrequency: ScheduledTaskFrequency::EVERY_MINUTE,
            allowedFrequencies: [ScheduledTaskFrequency::EVERY_MINUTE],
        );
        ScheduledTaskSetting::query()->create([
            'task_key' => $definition->key,
            'enabled' => true,
            'frequency' => $definition->defaultFrequency,
        ]);

        $registry = Mockery::mock(ScheduledTaskRegistry::class);
        $registry->shouldReceive('find')->once()->andReturn($definition);
        $registry->shouldReceive('execute')->once()->andThrow(new RuntimeException('Проверочная ошибка.'));

        try {
            (new ScheduledTaskRunner($registry))->run('test.failed');
            $this->fail('Исключение задачи не было проброшено.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Проверочная ошибка.', $exception->getMessage());
        }

        $this->assertSame(ScheduledTaskRun::STATUS_FAILED, ScheduledTaskSetting::query()->firstOrFail()->last_status);
        $this->assertDatabaseHas('scheduled_task_runs', [
            'task_key' => 'test.failed',
            'status' => ScheduledTaskRun::STATUS_FAILED,
            'trigger' => ScheduledTaskRun::TRIGGER_SCHEDULER,
            'error' => 'Проверочная ошибка.',
        ]);
    }

    public function test_manager_updates_only_an_allowed_frequency(): void
    {
        $definition = new ScheduledTaskDefinition(
            key: 'test.mutable',
            name: 'Настраиваемая',
            description: 'Настраиваемая задача',
            defaultFrequency: ScheduledTaskFrequency::EVERY_MINUTE,
            allowedFrequencies: [
                ScheduledTaskFrequency::EVERY_MINUTE,
                ScheduledTaskFrequency::EVERY_FIVE_MINUTES,
            ],
        );
        $registry = Mockery::mock(ScheduledTaskRegistry::class);
        $registry->shouldReceive('all')->once()->andReturn([$definition->key => $definition]);
        $registry->shouldReceive('find')->once()->with($definition->key)->andReturn($definition);

        $manager = new ScheduledTaskManager($registry);
        $manager->ensureSettings();
        $manager->update(
            key: $definition->key,
            enabled: false,
            frequency: ScheduledTaskFrequency::EVERY_FIVE_MINUTES->value,
            administratorId: null,
        );

        $setting = ScheduledTaskSetting::query()->firstOrFail();
        $this->assertFalse($setting->enabled);
        $this->assertSame(ScheduledTaskFrequency::EVERY_FIVE_MINUTES, $setting->frequency);
    }
}
