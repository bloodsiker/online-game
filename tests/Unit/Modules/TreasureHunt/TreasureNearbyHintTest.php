<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\TreasureHunt;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\TreasureHunt\Application\Services\TreasureHuntService;
use App\Modules\TreasureHunt\Application\Services\TreasureSearchRandom;
use Tests\TestCase;

class TreasureNearbyHintTest extends TestCase
{
    private function service(): TreasureHuntService
    {
        return new TreasureHuntService(
            $this->createMock(BackpackService::class),
            $this->createMock(ReputationService::class),
            $this->createMock(ChatService::class),
            new TreasureSearchRandom,
        );
    }

    public function test_location_is_adjacent_when_any_exit_leads_to_target(): void
    {
        $location = (new Location)->forceFill(['id' => 5, 'north' => 6, 'south' => 4, 'east' => 11, 'west' => 10, 'up' => null, 'down' => 99]);

        foreach ([6, 4, 11, 10, 99] as $target) {
            $this->assertTrue($this->service()->isAdjacent($location, $target), (string) $target);
        }
    }

    public function test_far_unknown_and_missing_locations_are_not_adjacent(): void
    {
        $location = (new Location)->forceFill(['id' => 5, 'north' => 6]);

        $this->assertFalse($this->service()->isAdjacent($location, 1));
        $this->assertFalse($this->service()->isAdjacent($location, 5));
        $this->assertFalse($this->service()->isAdjacent(null, 6));
    }

    public function test_nearby_hint_is_translated(): void
    {
        $this->assertSame('Клад уже где-то рядом...', __('treasure.nearby', [], 'ru'));
        $this->assertSame('Скарб уже десь поруч...', __('treasure.nearby', [], 'uk'));
    }
}
