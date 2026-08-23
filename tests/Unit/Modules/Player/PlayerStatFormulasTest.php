<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use PHPUnit\Framework\TestCase;

class PlayerStatFormulasTest extends TestCase
{
    public function test_intelligence_damage_percent_method_no_longer_exists(): void
    {
        $this->assertFalse(
            method_exists(PlayerStatFormulas::class, 'intelligenceDamagePercent'),
            'intelligenceDamagePercent() was superseded by MagicHitCalculator::power_coefficient and must be removed, not left dead.'
        );
    }

    public function test_strength_damage_percent_still_works(): void
    {
        // Regression guard: Step 1 must not touch strengthDamagePercent().
        $this->assertGreaterThan(0.0, PlayerStatFormulas::strengthDamagePercent(50.0, 12));
    }
}
