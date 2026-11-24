<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\Services\Battle\BattleActionLocks;
use App\Modules\Battle\Application\UseCases\RunAway;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class RunAwayDirectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            foreach (['north', 'south', 'east', 'west', 'up', 'down'] as $side) {
                $table->unsignedBigInteger($side)->nullable();
            }
        });
        DB::table('locations')->insert(['id' => 10, 'north' => 11, 'south' => 12, 'east' => 13, 'west' => 14, 'up' => 15, 'down' => 16]);
    }

    private function direction(int $from, int $to): ?string
    {
        $runAway = new RunAway($this->createMock(BattleActionLocks::class), $this->createMock(RandomizerInterface::class));
        $method = new ReflectionMethod($runAway, 'escapeDirection');

        return $method->invoke($runAway, $from, $to);
    }

    public function test_direction_is_the_side_that_leads_to_the_previous_location(): void
    {
        $this->assertSame('north', $this->direction(10, 11));
        $this->assertSame('south', $this->direction(10, 12));
        $this->assertSame('east', $this->direction(10, 13));
        $this->assertSame('west', $this->direction(10, 14));
        $this->assertSame('up', $this->direction(10, 15));
        $this->assertSame('down', $this->direction(10, 16));
    }

    public function test_direction_is_unknown_for_non_adjacent_or_missing_location(): void
    {
        $this->assertNull($this->direction(10, 99));
        $this->assertNull($this->direction(404, 11));
    }
}
