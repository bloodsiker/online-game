<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Scheduler;

use App\Modules\Scheduler\Domain\Enums\ScheduledTaskFrequency;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ScheduledTaskFrequencyTest extends TestCase
{
    public function test_it_calculates_the_next_sub_minute_run(): void
    {
        $now = CarbonImmutable::parse('2026-09-17 12:00:07');

        $this->assertSame(
            '2026-09-17 12:00:10',
            ScheduledTaskFrequency::EVERY_FIVE_SECONDS->nextRunAt($now)->format('Y-m-d H:i:s'),
        );
    }

    public function test_it_calculates_the_next_five_minute_run(): void
    {
        $now = CarbonImmutable::parse('2026-09-17 12:12:45');

        $this->assertSame(
            '2026-09-17 12:15:00',
            ScheduledTaskFrequency::EVERY_FIVE_MINUTES->nextRunAt($now)->format('Y-m-d H:i:s'),
        );
    }
}
