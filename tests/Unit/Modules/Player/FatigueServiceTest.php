<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\Player\Application\Services\FatigueService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Carbon\CarbonImmutable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FatigueServiceTest extends TestCase
{
    private FatigueService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('fatigue', [
            'max' => 100, 'regen_per_minute' => 3, 'sleep_minutes' => 5, 'wake_fatigue' => 50, 'warn_at' => 70,
            'costs' => ['training_dummy' => 1, 'heavy' => 40],
        ]);
        $this->service = new FatigueService;
    }

    public function test_fatigue_grows_by_source_cost(): void
    {
        $now = CarbonImmutable::parse('2026-11-23 12:00:00');
        $player = new Player;

        $this->service->add($player, 'training_dummy', 10, $now);

        $this->assertSame(10, $this->service->current($player, $now));
    }

    public function test_fatigue_regenerates_over_time_without_losing_fractions(): void
    {
        $start = CarbonImmutable::parse('2026-11-23 12:00:00');
        $player = new Player;
        $this->service->add($player, 'training_dummy', 30, $start);

        // 3/мин: за 5 минут — 15; частые обращения (каждые 10 с) не должны «съедать» остатки.
        for ($seconds = 10; $seconds <= 300; $seconds += 10) {
            $this->service->current($player, $start->addSeconds($seconds));
        }

        $this->assertSame(15, $this->service->current($player, $start->addMinutes(5)));
    }

    public function test_reaching_max_puts_player_to_sleep_and_blocks_actions(): void
    {
        $now = CarbonImmutable::parse('2026-11-23 12:00:00');
        $player = new Player;

        $fellAsleep = $this->service->add($player, 'heavy', 3, $now);

        $this->assertTrue($fellAsleep);
        $this->assertSame(100, $this->service->current($player, $now));
        $this->assertTrue($this->service->isAsleep($player, $now->addMinutes(4)));
        $this->assertSame(60, $this->service->sleepSecondsLeft($player, $now->addMinutes(4)));
    }

    public function test_player_wakes_up_with_reduced_fatigue(): void
    {
        $now = CarbonImmutable::parse('2026-11-23 12:00:00');
        $player = new Player;
        $this->service->add($player, 'heavy', 3, $now);

        $later = $now->addMinutes(6);

        $this->assertFalse($this->service->isAsleep($player, $later));
        $this->assertSame(50, $this->service->current($player, $later));
    }

    public function test_assert_awake_throws_conflict_while_sleeping(): void
    {
        $player = new Player;
        $this->service->add($player, 'heavy', 3);

        try {
            $this->service->assertAwake($player);
            $this->fail('Спящий игрок должен получить 409.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
    }
}
