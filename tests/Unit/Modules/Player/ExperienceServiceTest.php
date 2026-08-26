<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Tests\TestCase;

class ExperienceServiceTest extends TestCase
{
    public function test_player_has_neutral_experience_multiplier_by_default(): void
    {
        $player = new Player;

        $this->assertSame(1.0, $player->experience_multiplier);
        $this->assertSame(100, app(ExperienceService::class)->calculateGain($player, 100));
    }

    public function test_it_applies_both_experience_bonus_and_penalty(): void
    {
        $service = app(ExperienceService::class);
        $player = new Player;

        $player->experience_multiplier = 1.5;
        $this->assertSame(150, $service->calculateGain($player, 100));

        $player->experience_multiplier = 0.4;
        $this->assertSame(40, $service->calculateGain($player, 100));
    }

    public function test_it_never_turns_experience_gain_into_a_negative_value(): void
    {
        $player = new Player;
        $player->experience_multiplier = -2;

        $this->assertSame(0, app(ExperienceService::class)->calculateGain($player, 100));
        $this->assertSame(0, app(ExperienceService::class)->calculateGain($player, -100));
    }
}
