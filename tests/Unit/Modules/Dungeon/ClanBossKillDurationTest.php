<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dungeon;

use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use Tests\TestCase;

final class ClanBossKillDurationTest extends TestCase
{
    public function test_kill_duration_includes_all_elapsed_time_and_does_not_change_later(): void
    {
        $state = (new ClanBossRunStage)->forceFill([
            'status' => ClanBossRunStage::STATUS_KILLED,
            'started_at' => '2026-10-17 08:00:00',
            'killed_at' => '2026-10-18 10:15:30',
        ]);

        self::assertSame(94530, $state->killDurationSeconds());
        $this->travelTo(now()->addDays(10));
        self::assertSame(94530, $state->killDurationSeconds());
    }

    public function test_unfinished_and_expired_bosses_do_not_have_a_kill_duration(): void
    {
        foreach ([ClanBossRunStage::STATUS_ACTIVE, ClanBossRunStage::STATUS_EXPIRED, ClanBossRunStage::STATUS_SKIPPED] as $status) {
            $state = (new ClanBossRunStage)->forceFill([
                'status' => $status,
                'started_at' => '2026-10-17 08:00:00',
                'killed_at' => '2026-10-17 09:00:00',
            ]);
            self::assertNull($state->killDurationSeconds());
        }
    }

    public function test_missing_or_invalid_dates_are_not_presented_as_a_duration(): void
    {
        $state = (new ClanBossRunStage)->forceFill(['status' => ClanBossRunStage::STATUS_KILLED]);
        self::assertNull($state->killDurationSeconds());
        $state->started_at = '2026-10-17 08:00:00';
        self::assertNull($state->killDurationSeconds());
        $state->killed_at = '2026-10-17 07:00:00';
        self::assertNull($state->killDurationSeconds());
        $state->killed_at = '2026-10-17 08:00:00';
        self::assertSame(0, $state->killDurationSeconds());
    }
}
