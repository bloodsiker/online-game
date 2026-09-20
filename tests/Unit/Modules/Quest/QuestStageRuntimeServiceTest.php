<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Quest;

use App\Modules\Quest\Domain\Services\QuestStageRuntimeService;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Tests\TestCase;

class QuestStageRuntimeServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_waiting_stage_creates_hidden_ready_timestamp(): void
    {
        CarbonImmutable::setTestNow('2026-09-20 12:00:00');
        $stage = new QuestStage([
            'stage_type' => 'wait',
            'wait_duration_seconds' => 3600,
        ]);
        $stage->id = 15;

        $state = (new QuestStageRuntimeService)->stateFor($stage);

        $this->assertSame(15, $state['current_stage_id']);
        $this->assertSame('2026-09-20 13:00:00', $state['current_stage_ready_at']->format('Y-m-d H:i:s'));
    }

    public function test_waiting_message_contains_no_remaining_time(): void
    {
        CarbonImmutable::setTestNow('2026-09-20 12:00:00');
        $stage = new QuestStage([
            'stage_type' => 'wait',
            'waiting_text' => 'Ты пришёл слишком рано. Возвращайся позже.',
        ]);
        $progress = new QuestPlayer([
            'current_stage_ready_at' => '2026-09-20 13:00:00',
        ]);
        $progress->setRelation('currentStage', $stage);

        $message = (new QuestStageRuntimeService)->messageFor($progress);

        $this->assertSame('Ты пришёл слишком рано. Возвращайся позже.', $message);
        $this->assertStringNotContainsString('13:00', $message);
    }

    public function test_waiting_message_does_not_persist_missing_runtime_state(): void
    {
        $stage = new QuestStage([
            'stage_type' => 'wait',
            'waiting_text' => 'Возвращайся позже.',
        ]);
        $progress = new QuestPlayer;
        $progress->setRelation('currentStage', $stage);

        $message = (new QuestStageRuntimeService)->messageFor($progress);

        $this->assertSame('Возвращайся позже.', $message);
        $this->assertNull($progress->current_stage_ready_at);
        $this->assertFalse($progress->exists);
    }

    public function test_empty_waiting_stage_becomes_complete_only_when_ready(): void
    {
        CarbonImmutable::setTestNow('2026-09-20 12:00:00');
        $stage = new QuestStage(['stage_type' => 'wait']);
        $progress = new QuestPlayer(['current_stage_ready_at' => '2026-09-20 12:00:01']);
        $progress->setRelation('currentStage', $stage);
        $progress->setRelation('objectives', new Collection);

        $this->assertFalse($progress->isCurrentStageComplete());

        CarbonImmutable::setTestNow('2026-09-20 12:00:01');
        $this->assertTrue($progress->isCurrentStageComplete());
    }
}
