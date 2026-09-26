<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class WorldEventEditorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('lvl')->default(1);
        });
        Schema::create('world_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('objective_type');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('influence_map_id');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('monster_id')->nullable();
            $table->string('influence_name')->nullable();
            $table->timestamp('next_start_at')->nullable();
            $table->unsignedInteger('repeat_interval_minutes')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('global_limit');
            $table->unsignedInteger('player_limit');
            $table->unsignedInteger('spawn_limit');
            $table->unsignedInteger('respawn_seconds');
            $table->unsignedInteger('item_lifetime_minutes');
            $table->unsignedInteger('influence_per_item');
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
        Schema::create('world_event_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('world_event_id');
            $table->unsignedBigInteger('location_id');
        });

        DB::table('maps')->insert(['id' => 1, 'name' => 'Дартронг']);
        DB::table('monsters')->insert([
            ['id' => 1, 'name' => 'Складской вор', 'lvl' => 10],
            ['id' => 2, 'name' => 'Главарь воров', 'lvl' => 12],
        ]);

        Carbon::setTestNow('2026-09-22 12:00:00');
        Storage::fake('public');
        $this->withoutMiddleware(AdminMiddleware::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_saves_rich_description_and_event_image(): void
    {
        $this->post(route('admin.event.world-events.store'), [
            'title' => 'Складские воры',
            'description' => '<p><strong>Защитите склад</strong> от налётчиков.</p>',
            'image' => UploadedFile::fake()->image('warehouse-event.png', 120, 120),
            'map_id' => 1,
            'influence_map_id' => 1,
            'duration_minutes' => 60,
            'stages' => [
                [
                    'title' => 'Воры первой волны',
                    'objective_type' => 'kill',
                    'targets' => [
                        ['monster_id' => 1, 'spawn_weight' => 80, 'max_active' => 4],
                        ['monster_id' => 2, 'spawn_weight' => 20, 'max_active' => 1],
                    ],
                    'global_limit' => 50,
                    'player_limit' => 5,
                    'spawn_limit' => 5,
                    'respawn_seconds' => 60,
                    'item_lifetime_minutes' => 1440,
                    'influence_per_item' => 2,
                ],
                [
                    'title' => 'Главарь налётчиков',
                    'objective_type' => 'kill',
                    'targets' => [
                        ['monster_id' => 2, 'spawn_weight' => 100, 'max_active' => 1],
                    ],
                    'global_limit' => 1,
                    'player_limit' => 1,
                    'spawn_limit' => 1,
                    'respawn_seconds' => 60,
                    'item_lifetime_minutes' => 1440,
                    'influence_per_item' => 10,
                ],
            ],
            'is_active' => 1,
        ])->assertRedirect(route('admin.event.world-events.index'));

        $event = DB::table('world_events')->first();

        $this->assertSame('<p><strong>Защитите склад</strong> от налётчиков.</p>', $event->description);
        $this->assertMatchesRegularExpression('#^events/2026/09/.+\.png$#', $event->image);
        $this->assertDatabaseHas('world_event_stages', [
            'world_event_id' => $event->id,
            'position' => 1,
            'title' => 'Воры первой волны',
            'monster_id' => 1,
            'player_limit' => 5,
        ]);
        $this->assertDatabaseHas('world_event_stages', [
            'world_event_id' => $event->id,
            'position' => 2,
            'title' => 'Главарь налётчиков',
            'global_limit' => 1,
            'player_limit' => 1,
        ]);
        $this->assertDatabaseHas('world_event_stage_targets', [
            'world_event_stage_id' => 1,
            'monster_id' => 1,
            'spawn_weight' => 80,
            'max_active' => 4,
        ]);
        $this->assertDatabaseHas('world_event_stage_targets', [
            'world_event_stage_id' => 1,
            'monster_id' => 2,
            'spawn_weight' => 20,
            'max_active' => 1,
        ]);
        Storage::disk('public')->assertExists($event->image);
    }

    public function test_editor_uploads_an_embedded_image(): void
    {
        $response = $this->postJson(route('admin.event.world-events.upload-image'), [
            'file' => UploadedFile::fake()->image('description.png', 300, 200),
        ]);

        $response->assertOk()->assertJsonStructure(['url']);
        $this->assertStringContainsString('/storage/events/content/2026/09/', (string) $response->json('url'));

        $files = Storage::disk('public')->allFiles('events/content/2026/09');
        $this->assertCount(1, $files);
    }
}
