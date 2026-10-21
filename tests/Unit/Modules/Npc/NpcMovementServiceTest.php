<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Npc;

use App\Modules\Npc\Application\Services\NpcMovementService;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcMovement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class NpcMovementServiceTest extends TestCase
{
    private NpcMovementService $service;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.npc_movement_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ], 'database.default' => 'npc_movement_test']);
        DB::purge('npc_movement_test');
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('dungeon_id')->nullable();
            $table->boolean('is_locked')->default(false);
            foreach (['north', 'south', 'east', 'west', 'up', 'down'] as $direction) {
                $table->unsignedBigInteger($direction)->nullable();
            }
        });
        Schema::create('npcs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_17_000001_create_npc_movements_table.php'))->up();

        DB::table('maps')->insert([['id' => 1], ['id' => 2]]);
        DB::table('locations')->insert([
            ['id' => 10, 'map_id' => 1, 'dungeon_id' => null],
            ['id' => 11, 'map_id' => 1, 'dungeon_id' => null],
            ['id' => 12, 'map_id' => 1, 'dungeon_id' => 1],
            ['id' => 13, 'map_id' => 1, 'dungeon_id' => null],
            ['id' => 20, 'map_id' => 2, 'dungeon_id' => null],
        ]);
        DB::table('locations')->where('id', 10)->update(['north' => 11, 'west' => 20, 'up' => 12]);
        DB::table('locations')->where('id', 11)->update(['south' => 10]);
        DB::table('locations')->where('id', 20)->update(['east' => 10]);
        DB::table('npcs')->insert(['id' => 1, 'location_id' => 10, 'is_active' => true]);
        $this->service = new NpcMovementService;
        $this->now = CarbonImmutable::parse('2026-10-17 12:00:00');
        $this->travelTo($this->now);
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        DB::purge('npc_movement_test');
        parent::tearDown();
    }

    public function test_map_movement_excludes_current_location_other_maps_and_dungeons(): void
    {
        $this->configure();
        self::assertSame(0, $this->service->moveDue($this->now->addMinutes(9)));
        self::assertSame(1, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertSame(11, (int) Npc::findOrFail(1)->location_id);
        self::assertTrue(NpcMovement::firstOrFail()->next_move_at->equalTo($this->now->addMinutes(20)));
    }

    public function test_list_mode_can_use_different_maps_and_never_chooses_an_unlisted_location(): void
    {
        $this->configure(['mode' => 'locations', 'location_ids' => ['10', '20']]);
        self::assertSame([10, 20], NpcMovement::firstOrFail()->location_ids);
        self::assertNull(NpcMovement::firstOrFail()->map_id);
        self::assertSame(1, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertSame(20, (int) Npc::findOrFail(1)->location_id);
    }

    public function test_repeated_tick_does_not_move_twice_and_does_not_catch_up_missed_steps(): void
    {
        $this->configure();
        $late = $this->now->addHours(2);
        self::assertSame(1, $this->service->moveDue($late));
        self::assertSame(0, $this->service->moveDue($late));
        self::assertSame(11, (int) Npc::findOrFail(1)->location_id);
        self::assertTrue(NpcMovement::firstOrFail()->next_move_at->equalTo($late->addMinutes(10)));
    }

    public function test_each_tick_takes_only_one_step_using_the_new_current_location(): void
    {
        $this->configure();
        self::assertSame(1, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertSame(11, (int) Npc::findOrFail(1)->location_id);
        self::assertSame(1, $this->service->moveDue($this->now->addMinutes(20)));
        self::assertSame(10, (int) Npc::findOrFail(1)->location_id);
    }

    public function test_disconnected_allowed_locations_are_not_teleport_destinations(): void
    {
        $this->configure(['mode' => 'locations', 'location_ids' => [10, 13]]);
        self::assertSame(0, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertSame(10, (int) Npc::findOrFail(1)->location_id);
        self::assertTrue(NpcMovement::firstOrFail()->next_move_at->equalTo($this->now->addMinutes(20)));
    }

    public function test_dead_end_and_locked_neighbour_do_not_cause_a_jump(): void
    {
        $this->configure();
        DB::table('locations')->where('id', 11)->update(['is_locked' => true]);
        self::assertSame(0, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertSame(10, (int) Npc::findOrFail(1)->location_id);

        DB::table('locations')->where('id', 10)->update(['north' => null, 'west' => null, 'up' => null]);
        self::assertSame(0, $this->service->moveDue($this->now->addMinutes(20)));
        self::assertSame(10, (int) Npc::findOrFail(1)->location_id);
    }

    public function test_npc_without_a_current_location_is_not_teleported(): void
    {
        $this->configure();
        Npc::whereKey(1)->update(['location_id' => null]);
        self::assertSame(0, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertNull(Npc::findOrFail(1)->location_id);
    }

    public function test_vertical_passages_are_supported(): void
    {
        $this->configure();
        DB::table('locations')->where('id', 10)->update(['north' => null, 'up' => 13]);
        DB::table('locations')->where('id', 13)->update(['down' => 10]);
        self::assertSame(1, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertSame(13, (int) Npc::findOrFail(1)->location_id);
        self::assertSame(1, $this->service->moveDue($this->now->addMinutes(20)));
        self::assertSame(10, (int) Npc::findOrFail(1)->location_id);
    }

    public function test_random_choice_is_limited_to_allowed_neighbours(): void
    {
        $this->configure();
        DB::table('locations')->where('id', 10)->update(['east' => 13]);
        self::assertSame(1, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertContains((int) Npc::findOrFail(1)->location_id, [11, 13]);
    }

    public function test_disabled_movement_and_inactive_npc_stay_in_place(): void
    {
        $this->configure(['enabled' => false]);
        self::assertNull(NpcMovement::firstOrFail()->next_move_at);
        self::assertSame(0, $this->service->moveDue($this->now->addHours(1)));
        $this->configure();
        Npc::whereKey(1)->update(['is_active' => false]);
        self::assertSame(0, $this->service->moveDue($this->now->addHours(1)));
        self::assertSame(10, (int) Npc::findOrFail(1)->location_id);
    }

    public function test_deleted_map_does_not_allow_movement_across_the_whole_world(): void
    {
        $this->configure();
        DB::table('maps')->where('id', 1)->delete();
        self::assertNull(NpcMovement::firstOrFail()->map_id);
        self::assertSame(0, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertSame(10, (int) Npc::findOrFail(1)->location_id);
    }

    public function test_missing_destinations_are_skipped_and_retried_later(): void
    {
        $this->configure(['mode' => 'locations', 'location_ids' => [10, 20]]);
        DB::table('locations')->where('id', 20)->delete();
        self::assertSame(0, $this->service->moveDue($this->now->addMinutes(10)));
        self::assertTrue(NpcMovement::firstOrFail()->next_move_at->equalTo($this->now->addMinutes(20)));
    }

    public function test_reconfiguration_reuses_settings_and_switches_the_route(): void
    {
        $this->configure();
        $this->configure(['mode' => 'locations', 'location_ids' => [10, 20]]);
        self::assertSame(1, NpcMovement::count());
        self::assertNull(NpcMovement::firstOrFail()->map_id);
        $this->configure(['enabled' => false, 'mode' => 'locations', 'location_ids' => [10, 20]]);
        self::assertSame([10, 20], NpcMovement::firstOrFail()->location_ids);
        self::assertNull(NpcMovement::firstOrFail()->next_move_at);
    }

    public function test_invalid_settings_are_rejected_without_writing(): void
    {
        foreach ([
            ['mode' => 'unknown'],
            ['map_id' => 999],
            ['interval_minutes' => 0],
            ['interval_minutes' => 10081],
            ['mode' => 'locations', 'location_ids' => [10]],
            ['mode' => 'locations', 'location_ids' => [10, 10]],
            ['mode' => 'locations', 'location_ids' => [10, 12]],
            ['mode' => 'locations', 'location_ids' => [10, 999]],
        ] as $input) {
            try {
                $this->configure($input);
                self::fail('Invalid movement settings must be rejected.');
            } catch (ValidationException) {
                self::assertSame(0, NpcMovement::count());
            }
        }
    }

    private function configure(array $overrides = []): void
    {
        $this->service->configure(Npc::findOrFail(1), array_replace([
            'enabled' => true,
            'mode' => 'map',
            'map_id' => 1,
            'interval_minutes' => 10,
        ], $overrides));
    }
}
