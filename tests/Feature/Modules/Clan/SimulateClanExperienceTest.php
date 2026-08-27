<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use Tests\TestCase;

class SimulateClanExperienceTest extends TestCase
{
    public function test_simulation_uses_the_actual_level_difference_bonus_and_personal_experience_multiplier(): void
    {
        $options = [
            '--players' => 1,
            '--hours-per-day' => 1,
            '--rounds-per-kill' => 1,
            '--sec-per-round' => 1,
            '--level-offset' => 5,
            '--milestone-days' => '1',
            '--max-level' => 100,
        ];

        $this->artisan('clan:simulate-experience', $options)
            ->expectsOutputToContain('фарм +5 lvl (зона 3% в клан) | личный опыт ×1.00')
            ->expectsOutputToContain('347 011')
            ->assertExitCode(0);

        $this->artisan('clan:simulate-experience', [...$options, '--experience-multiplier' => 2])
            ->expectsOutputToContain('личный опыт ×2.00')
            ->expectsOutputToContain('906 801')
            ->assertExitCode(0);
    }

    public function test_zero_level_offset_is_reported_as_the_one_percent_clan_experience_zone(): void
    {
        $this->artisan('clan:simulate-experience', [
            '--players' => 1,
            '--hours-per-day' => 1,
            '--level-offset' => 0,
            '--milestone-days' => '1',
            '--max-level' => 100,
        ])
            ->expectsOutputToContain('фарм +0 lvl (зона 1% в клан)')
            ->assertExitCode(0);
    }
}
