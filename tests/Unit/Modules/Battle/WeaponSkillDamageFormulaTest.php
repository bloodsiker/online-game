<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use Mockery;
use Tests\TestCase;

class WeaponSkillDamageFormulaTest extends TestCase
{
    public function test_weapon_skill_multiplier_uses_the_soft_cap_formula(): void
    {
        $this->assertSame(1.0, HitCalculator::weaponSkillMultiplier(0));
        $this->assertSame(1.5, HitCalculator::weaponSkillMultiplier(300));
        $this->assertEqualsWithDelta(1.769230769, HitCalculator::weaponSkillMultiplier(1000), 0.000001);
    }

    public function test_hit_calculator_applies_weapon_skill_before_defense(): void
    {
        $random = Mockery::mock(RandomizerInterface::class);
        $random->shouldReceive('between')->once()->with(100, 100)->andReturn(100);
        $random->shouldReceive('chance')->twice()->andReturn(false);

        $attacker = Mockery::mock(FightHitInterface::class)->shouldIgnoreMissing();
        $defender = Mockery::mock(FightHitInterface::class)->shouldIgnoreMissing();

        $hit = (new HitCalculator($random))->hit($attacker, $defender, 100, 100, 300);

        $this->assertSame(150, $hit->getDamage());
    }
}
