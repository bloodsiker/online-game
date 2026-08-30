<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MapGatheringResourceConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();
        $this->seedConfiguration();
        $this->withoutMiddleware(AdminMiddleware::class);
    }

    public function test_admin_can_edit_existing_resource_configuration_on_map(): void
    {
        $response = $this->patch(route('admin.map.gathering-resource.update', [2, 10]), [
            'max_active' => 2,
            'min_x' => 20,
            'max_x' => 30,
            'min_y' => 40,
            'max_y' => 50,
        ]);

        $response->assertRedirect(route('admin.map.info', 2).'#tab-resources');
        $this->assertDatabaseHas('map_gathering_resources', [
            'id' => 10,
            'map_id' => 2,
            'max_active' => 2,
            'min_x' => 20,
            'max_x' => 30,
            'min_y' => 40,
            'max_y' => 50,
        ]);
        $this->assertSame(2, DB::table('gathering_nodes')->where('map_gathering_resource_id', 10)->count());

        DB::table('gathering_nodes')
            ->where('map_gathering_resource_id', 10)
            ->get()
            ->each(function (object $node): void {
                $this->assertGreaterThanOrEqual(20, (float) $node->x_percent);
                $this->assertLessThanOrEqual(30, (float) $node->x_percent);
                $this->assertGreaterThanOrEqual(40, (float) $node->y_percent);
                $this->assertLessThanOrEqual(50, (float) $node->y_percent);
            });
    }

    public function test_map_page_shows_inline_edit_form_for_each_added_resource(): void
    {
        $this->get(route('admin.map.info', 2))
            ->assertOk()
            ->assertSee(route('admin.map.gathering-resource.update', [2, 10]), escape: false)
            ->assertSee('name="max_active"', escape: false)
            ->assertSee('name="min_x"', escape: false)
            ->assertSee('name="max_y"', escape: false)
            ->assertSee('Сохранить')
            ->assertSee('Предмет');
    }

    public function test_configuration_cannot_be_edited_through_another_map(): void
    {
        DB::table('maps')->insert(['id' => 3, 'name' => 'Другая карта']);

        $this->patch(route('admin.map.gathering-resource.update', [3, 10]), [
            'max_active' => 1,
            'min_x' => 1,
            'max_x' => 99,
            'min_y' => 1,
            'max_y' => 99,
        ])->assertNotFound();
    }

    private function seedConfiguration(): void
    {
        DB::table('maps')->insert(['id' => 2, 'name' => 'Шепчущий Лес']);
        DB::table('skills')->insert(['id' => 6, 'name' => 'Травник']);
        DB::table('share_items')->insert([
            [
                'id' => 99,
                'type' => 'tool',
                'name' => 'Серп',
                'skill_id' => null,
                'skill_lvl' => null,
                'gathering_time_seconds' => null,
                'gathering_respawn_seconds' => null,
                'tool_family' => 'sickle',
                'gathering_tool_family' => null,
            ],
            [
                'id' => 100,
                'type' => 'resource',
                'name' => 'Лечебная трава',
                'skill_id' => 6,
                'skill_lvl' => 1,
                'gathering_time_seconds' => 5,
                'gathering_respawn_seconds' => 30,
                'tool_family' => null,
                'gathering_tool_family' => 'sickle',
            ],
        ]);
        DB::table('map_gathering_resources')->insert([
            'id' => 10,
            'map_id' => 2,
            'share_item_id' => 100,
            'max_active' => 3,
            'min_x' => 8,
            'max_x' => 92,
            'min_y' => 8,
            'max_y' => 92,
        ]);
        DB::table('gathering_nodes')->insert([
            ['id' => 20, 'map_gathering_resource_id' => 10, 'x_percent' => 10, 'y_percent' => 10],
            ['id' => 21, 'map_gathering_resource_id' => 10, 'x_percent' => 50, 'y_percent' => 50],
            ['id' => 22, 'map_gathering_resource_id' => 10, 'x_percent' => 90, 'y_percent' => 90],
        ]);
    }

    private function createTables(): void
    {
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('folder')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id')->nullable();
            $table->string('name');
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('skill_lvl')->nullable();
            $table->integer('gathering_time_seconds')->nullable();
            $table->integer('gathering_respawn_seconds')->nullable();
            $table->string('tool_family')->nullable();
            $table->unsignedTinyInteger('gathering_speed_bonus_percent')->default(0);
            $table->string('gathering_tool_family')->nullable();
        });
        Schema::create('map_gathering_resources', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('max_active');
            $table->integer('min_x');
            $table->integer('max_x');
            $table->integer('min_y');
            $table->integer('max_y');
            $table->timestamps();
        });
        Schema::create('gathering_nodes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_gathering_resource_id');
            $table->decimal('x_percent', 5, 2);
            $table->decimal('y_percent', 5, 2);
            $table->timestamp('respawn_at')->nullable();
            $table->timestamps();
        });
        Schema::create('gathering_attempts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('gathering_node_id');
            $table->timestamps();
        });
    }
}
