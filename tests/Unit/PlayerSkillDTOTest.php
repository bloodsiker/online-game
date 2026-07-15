<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Player\Application\DTOs\PlayerSkillDTO;
use PHPUnit\Framework\TestCase;

class PlayerSkillDTOTest extends TestCase
{
    public function test_exp_percent_uses_current_level_range(): void
    {
        $skill = new PlayerSkillDTO(
            name: 'Мечи',
            level: 3,
            exp: 2500,
            expUp: 3000,
            expDiff: 1000,
        );

        $this->assertSame(50.0, $skill->expPercent());
    }

    public function test_exp_percent_is_clamped_to_current_level_range(): void
    {
        $this->assertSame(0.0, (new PlayerSkillDTO('Мечи', 3, 1500, 3000, 1000))->expPercent());
        $this->assertSame(100.0, (new PlayerSkillDTO('Мечи', 3, 3500, 3000, 1000))->expPercent());
    }
}
