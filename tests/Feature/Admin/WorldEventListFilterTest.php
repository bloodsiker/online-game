<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class WorldEventListFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createSchema();
        $this->withoutMiddleware(AdminMiddleware::class);

        DB::table('maps')->insert([
            ['id' => 1, 'name' => 'Город'],
            ['id' => 2, 'name' => 'Канализация'],
            ['id' => 3, 'name' => 'Кладбище'],
        ]);
        $this->insertEvent(1, 'Травы города', 1, 1);
        $this->insertEvent(2, 'Крысы канализации', 2, 1);
        $this->insertEvent(3, 'Духи кладбища', 3, 3);
        $this->insertEvent(4, 'Старое событие', 2, null);
    }

    public function test_events_can_be_filtered_by_event_map(): void
    {
        $response = $this->get(route('admin.event.world-events.index', ['map_id' => 2]));

        $response->assertOk()
            ->assertSee('Крысы канализации')
            ->assertSee('Старое событие')
            ->assertDontSee('Травы города')
            ->assertDontSee('Духи кладбища');
    }

    public function test_events_can_be_filtered_by_influence_with_legacy_fallback(): void
    {
        $response = $this->get(route('admin.event.world-events.index', ['influence_map_id' => 1]));

        $response->assertOk()
            ->assertSee('Травы города')
            ->assertSee('Крысы канализации')
            ->assertDontSee('Старое событие')
            ->assertDontSee('Духи кладбища');

        $this->get(route('admin.event.world-events.index', ['influence_map_id' => 2]))
            ->assertOk()
            ->assertSee('Старое событие');
    }

    public function test_events_can_be_filtered_by_stage_target_item(): void
    {
        DB::table('share_items')->insert(['id' => 1746, 'name' => 'Событийный предмет']);
        DB::table('world_event_stages')->insert([
            'id' => 10,
            'world_event_id' => 1,
            'position' => 1,
            'title' => 'Сбор предметов',
            'objective_type' => 'collect',
            'global_limit' => 50,
            'player_limit' => 5,
            'spawn_limit' => 10,
            'respawn_seconds' => 60,
            'item_lifetime_minutes' => 60,
            'influence_per_item' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('world_event_stage_targets')->insert([
            'world_event_stage_id' => 10,
            'share_item_id' => 1746,
            'position' => 1,
            'spawn_weight' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('admin.event.world-events.index', ['share_item_id' => 1746]))
            ->assertOk()
            ->assertSee('Травы города')
            ->assertSee('Событийный предмет')
            ->assertDontSee('Крысы канализации')
            ->assertDontSee('Духи кладбища');
    }

    private function insertEvent(int $id, string $title, int $mapId, ?int $influenceMapId): void
    {
        DB::table('world_events')->insert([
            'id' => $id,
            'title' => $title,
            'objective_type' => 'kill',
            'map_id' => $mapId,
            'influence_map_id' => $influenceMapId,
            'duration_minutes' => 60,
            'global_limit' => 50,
            'player_limit' => 5,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('world_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('objective_type');
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('influence_map_id')->nullable();
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('monster_id')->nullable();
            $table->timestamp('next_start_at')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('global_limit');
            $table->unsignedInteger('player_limit');
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('world_event_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_id');
            $table->unsignedSmallInteger('position');
            $table->string('title');
            $table->string('objective_type');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('monster_id')->nullable();
            $table->unsignedInteger('global_limit');
            $table->unsignedInteger('player_limit');
            $table->unsignedInteger('spawn_limit');
            $table->unsignedInteger('respawn_seconds');
            $table->unsignedInteger('item_lifetime_minutes');
            $table->unsignedInteger('influence_per_item');
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('world_event_stage_targets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_stage_id');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('monster_id')->nullable();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('spawn_weight');
            $table->unsignedInteger('max_active')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('world_event_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_id');
            $table->unsignedBigInteger('location_id');
        });
        Schema::create('world_event_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_id');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->string('status');
            $table->unsignedInteger('collected_count')->default(0);
            $table->timestamps();
        });
    }
}
