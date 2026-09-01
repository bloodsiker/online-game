<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\ReputationExchange;

use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationExchange;
use Tests\TestCase;

class ReputationExchangeTest extends TestCase
{
    public function test_all_relics_are_accepted_before_first_reputation_threshold(): void
    {
        $highTierRelic = (new ReputationExchange)->forceFill([
            'min_reputation' => 2_000,
            'max_reputation' => 2_500,
        ]);

        $this->assertTrue($highTierRelic->isAcceptedAt(0));
        $this->assertTrue($highTierRelic->isAcceptedAt(499));
    }

    public function test_low_tier_relic_is_rejected_at_one_thousand_reputation(): void
    {
        $lowTierRelic = (new ReputationExchange)->forceFill([
            'min_reputation' => 0,
            'max_reputation' => 500,
        ]);
        $thousandTierRelic = (new ReputationExchange)->forceFill([
            'min_reputation' => 1_000,
            'max_reputation' => 1_500,
        ]);

        $this->assertFalse($lowTierRelic->isAcceptedAt(1_000));
        $this->assertTrue($thousandTierRelic->isAcceptedAt(1_000));
    }
}
