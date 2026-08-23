<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use PHPUnit\Framework\TestCase;
use Tests\Support\StubCombatant;

class MagicHitCalculatorTest extends TestCase
{
    public function test_damage_with_no_intelligence_or_resistance_equals_base_roll(): void
    {
        $calc = new MagicHitCalculator;
        $attacker = new StubCombatant(level: 12);
        $defender = new StubCombatant(level: 12);

        $hit = $calc->hit($attacker, $defender, minDamage: 10, maxDamage: 10, powerCoefficient: 0.3);

        $this->assertSame(10, $hit->getDamage());
        $this->assertFalse($hit->isDodge());
        $this->assertFalse($hit->isCritical());
    }

    public function test_intelligence_and_magic_attack_both_add_to_raw_damage(): void
    {
        $calc = new MagicHitCalculator;
        // magic_power = 21 (intelligence) + 9 (gear) = 30; 30 * 0.3 = 9 bonus
        $attacker = new StubCombatant(level: 12, intelligence: 21, magicAttack: 9);
        $defender = new StubCombatant(level: 12);

        $hit = $calc->hit($attacker, $defender, minDamage: 10, maxDamage: 10, powerCoefficient: 0.3);

        $this->assertSame(19, $hit->getDamage());
    }

    public function test_magic_resistance_mitigates_like_armor_softcap(): void
    {
        $calc = new MagicHitCalculator;
        $attacker = new StubCombatant(level: 12);
        // damageMultiplier = 220 / (220 + 220) = 0.5 at reference level 12
        $defender = new StubCombatant(level: 12, magicResistance: 220);

        $hit = $calc->hit($attacker, $defender, minDamage: 100, maxDamage: 100, powerCoefficient: 0.3);

        $this->assertSame(50, $hit->getDamage());
    }

    public function test_damage_never_drops_below_one(): void
    {
        $calc = new MagicHitCalculator;
        $attacker = new StubCombatant(level: 12);
        $defender = new StubCombatant(level: 12, magicResistance: 100000);

        $hit = $calc->hit($attacker, $defender, minDamage: 1, maxDamage: 1, powerCoefficient: 0.0);

        $this->assertSame(1, $hit->getDamage());
    }

    public function test_heal_ignores_target_resistance_entirely(): void
    {
        $calc = new MagicHitCalculator;
        $caster = new StubCombatant(level: 12, intelligence: 21);

        // magic_power = 21 * 0.4 = 8.4 -> round 8; base 50 -> 58
        $healed = $calc->heal($caster, minHeal: 50, maxHeal: 50, powerCoefficient: 0.4);

        $this->assertSame(58, $healed);
    }
}
