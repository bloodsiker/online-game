<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\ReputationExchange;

use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Structure\ReputationExchange\Application\DTOs\GambleExchangePageDTO;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangePageDTO;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationExchange;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class ReputationExchangeTest extends TestCase
{
    public function test_progress_is_calculated_between_reputation_tiers(): void
    {
        $reputation = new Reputation;
        $reputation->setRelation('tiers', new Collection([
            (new ReputationTier)->forceFill(['min_points' => 500, 'max_points' => 1000]),
            (new ReputationTier)->forceFill(['min_points' => 1000, 'max_points' => null]),
        ]));
        $service = app(ReputationService::class);
        $this->assertEquals(['percent' => 4, 'title' => '20 из 500'], $service->getProgress($reputation, 20));
        $this->assertEquals(['percent' => 20, 'title' => '100 из 500'], $service->getProgress($reputation, 600));
        $this->assertEquals(0, $service->getProgress($reputation, 500)['percent']);
        $this->assertEquals(100, $service->getProgress($reputation, 1000)['percent']);
    }

    public function test_exchange_page_renders_compact_progress_bar(): void
    {
        $page = new ReputationExchangePageDTO(
            structureId: 31, reputationName: 'Бог Мёртвых', currentPoints: 20,
            items: [], progressPercent: 4, progressTitle: '20 из 500',
        );
        $html = view('reputation_exchange::index', ['page' => $page, 'itemTooltipScript' => ''])->render();
        $this->assertStringContainsString('rep-progress-bar--small', $html);
        $this->assertStringContainsString('title="20 из 500"', $html);
        $this->assertStringContainsString('width:4%', $html);
    }

    public function test_gamble_exchange_page_renders_compact_progress_bar(): void
    {
        $page = new GambleExchangePageDTO(
            structureId: 31, reputationName: 'Бог Мёртвых', currentPoints: 20,
            resources: [], offeringCooldown: null, progressPercent: 4, progressTitle: '20 из 500',
        );
        $html = view('reputation_exchange::gamble', ['page' => $page, 'itemTooltipScript' => ''])->render();
        $this->assertStringContainsString('rep-progress-bar--small', $html);
        $this->assertStringContainsString('title="20 из 500"', $html);
        $this->assertStringContainsString('width:4%', $html);
    }

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
