<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Location;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use PHPUnit\Framework\TestCase;

class LocationMonsterRangeTest extends TestCase
{
    private function location(int $min, int $max): Location
    {
        return (new Location)->forceFill(['min_monster' => $min, 'count_monster' => $max]);
    }

    public function test_range_two_to_five_gives_between_two_and_five_monsters_total(): void
    {
        $location = $this->location(2, 5);

        for ($i = 0; $i < 500; $i++) {
            foreach ([0, 1, 2, 3, 4] as $current) {
                $total = $current + $location->monstersToSpawn($current);
                $this->assertGreaterThanOrEqual(max(2, $current + 1), $total);
                $this->assertLessThanOrEqual(5, $total);
            }
        }
    }

    public function test_nothing_to_spawn_when_location_is_full(): void
    {
        $this->assertSame(0, $this->location(2, 5)->monstersToSpawn(5));
        $this->assertSame(0, $this->location(1, 0)->monstersToSpawn(0));
    }

    public function test_default_minimum_keeps_legacy_behaviour(): void
    {
        $location = $this->location(1, 3);
        $seen = [];
        for ($i = 0; $i < 300; $i++) {
            $seen[$location->monstersToSpawn(1)] = true;
        }

        $values = array_keys($seen);
        sort($values);

        $this->assertSame([1, 2], $values);
    }

    public function test_minimum_above_maximum_is_clamped(): void
    {
        $location = $this->location(9, 3);

        for ($i = 0; $i < 100; $i++) {
            $this->assertSame(3, $location->monstersToSpawn(0));
        }
    }
}
