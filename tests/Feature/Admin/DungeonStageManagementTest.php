<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DungeonStageManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createSchema();
        DB::table('dungeons')->insert(['id' => 1, 'name' => 'Башня', 'type' => 'tower']);
        DB::table('locations')->insert([
            ['id' => 10, 'name' => 'Клетка 1', 'dungeon_id' => 1],
            ['id' => 11, 'name' => 'Клетка 2', 'dungeon_id' => 1],
        ]);
        DB::table('monsters')->insert([
            ['id' => 20, 'name' => 'Страж', 'lvl' => 10],
            ['id' => 21, 'name' => 'Лучник', 'lvl' => 11],
        ]);

        $this->withoutMiddleware(AdminMiddleware::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_saves_multiple_weighted_monsters_for_distributed_floor(): void
    {
        $this->post(route('admin.dungeon.stage.save', 1), [
            'number' => 1,
            'name' => 'Первый этаж',
            'time_limit_seconds' => 180,
            'spawn_type' => 'distributed',
            'total_monsters' => 30,
            'location_ids' => [10, 11],
            'entry_location_id' => 10,
            'monsters' => [
                ['monster_id' => 20, 'quantity' => 1, 'weight' => 3, 'location_id' => 10],
                ['monster_id' => 21, 'quantity' => 1, 'weight' => 1, 'location_id' => 11],
            ],
        ])->assertSessionHasNoErrors();

        $stageId = DB::table('dungeon_stages')->value('id');
        $this->assertSame(30, DB::table('dungeon_stages')->where('id', $stageId)->value('total_monsters'));
        $this->assertDatabaseHas('dungeon_stage_monsters', [
            'dungeon_stage_id' => $stageId,
            'monster_id' => 20,
            'location_id' => null,
            'weight' => 3,
        ]);
        $this->assertDatabaseHas('dungeon_stage_monsters', [
            'dungeon_stage_id' => $stageId,
            'monster_id' => 21,
            'location_id' => null,
            'weight' => 1,
        ]);
    }

    public function test_fixed_floor_uses_per_monster_quantities_and_locations(): void
    {
        $this->post(route('admin.dungeon.stage.save', 1), [
            'number' => 2,
            'name' => 'Второй этаж',
            'time_limit_seconds' => 180,
            'spawn_type' => 'fixed',
            'total_monsters' => 1,
            'location_ids' => [10, 11],
            'entry_location_id' => 10,
            'monsters' => [
                ['monster_id' => 20, 'quantity' => 7, 'weight' => 1, 'location_id' => 10],
                ['monster_id' => 21, 'quantity' => 5, 'weight' => 1, 'location_id' => 11],
            ],
        ])->assertSessionHasNoErrors();

        $stage = DB::table('dungeon_stages')->first();
        $this->assertSame(12, $stage->total_monsters);
        $this->assertDatabaseHas('dungeon_stage_monsters', [
            'dungeon_stage_id' => $stage->id,
            'monster_id' => 20,
            'location_id' => 10,
            'quantity' => 7,
        ]);
        $this->assertDatabaseHas('dungeon_stage_monsters', [
            'dungeon_stage_id' => $stage->id,
            'monster_id' => 21,
            'location_id' => 11,
            'quantity' => 5,
        ]);
    }

    public function test_monster_cannot_be_assigned_to_location_outside_selected_floor(): void
    {
        $this->from('/admin/dungeon/1')->post(route('admin.dungeon.stage.save', 1), [
            'number' => 1,
            'name' => 'Первый этаж',
            'time_limit_seconds' => 180,
            'spawn_type' => 'fixed',
            'total_monsters' => 1,
            'location_ids' => [10],
            'entry_location_id' => 10,
            'monsters' => [
                ['monster_id' => 20, 'quantity' => 1, 'weight' => 1, 'location_id' => 11],
            ],
        ])->assertRedirect('/admin/dungeon/1')
            ->assertSessionHasErrors('monsters.0.location_id');

        $this->assertDatabaseCount('dungeon_stages', 0);
    }

    private function createSchema(): void
    {
        Schema::create('dungeons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('dungeon_id')->nullable();
            $table->timestamps();
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('lvl');
            $table->timestamps();
        });
        Schema::create('dungeon_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedSmallInteger('number');
            $table->string('name');
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->string('completion_type');
            $table->string('spawn_type');
            $table->unsignedInteger('total_monsters');
            $table->unsignedBigInteger('next_stage_id')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_stage_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('location_id');
            $table->boolean('is_entry');
            $table->unsignedSmallInteger('position');
            $table->timestamps();
        });
        Schema::create('dungeon_stage_monsters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedSmallInteger('weight');
            $table->timestamps();
        });
    }
}
