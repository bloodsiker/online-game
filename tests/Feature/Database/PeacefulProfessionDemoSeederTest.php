<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Database\Seeders\PeacefulProfessionDemoSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PeacefulProfessionDemoSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();
        $this->seedPrerequisites();
    }

    public function test_it_idempotently_adds_three_resources_to_whispering_forest_and_tools_to_first_player(): void
    {
        $seeder = new PeacefulProfessionDemoSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertSame(6, DB::table('share_items')->count());
        $this->assertSame(3, DB::table('share_items')->where('type', 'resource')->count());
        $this->assertSame(3, DB::table('share_items')->where('type', 'tool')->count());
        $this->assertSame(3, DB::table('map_gathering_resources')->where('map_id', 2)->count());
        $this->assertSame(3, DB::table('backpacks')->where('user_id', 1)->count());
        $this->assertSame(3, DB::table('items')->count());

        $this->assertDatabaseHas('map_gathering_resources', [
            'map_id' => 2,
            'max_active' => 1,
        ]);
        $this->assertDatabaseHas('share_items', [
            'name' => 'Лечебная трава',
            'skill_id' => 6,
            'gathering_time_seconds' => 5,
            'gathering_respawn_seconds' => 30,
        ]);
        $this->assertDatabaseHas('share_items', [
            'name' => 'Речной окунь',
            'skill_id' => 7,
        ]);
        $this->assertDatabaseHas('share_items', [
            'name' => 'Медная руда',
            'skill_id' => 8,
        ]);
    }

    private function seedPrerequisites(): void
    {
        DB::table('maps')->insert(['id' => 2, 'name' => 'Шепчущий Лес']);
        DB::table('players')->insert(['id' => 1, 'user_id' => 1]);
        DB::table('skills')->insert([
            ['id' => 6, 'name' => 'Травник', 'type' => 'peaceful'],
            ['id' => 7, 'name' => 'Рыбак', 'type' => 'peaceful'],
            ['id' => 8, 'name' => 'Геолог', 'type' => 'peaceful'],
        ]);
    }

    private function createTables(): void
    {
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_two_hand')->default(false);
            $table->integer('count_use')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_slot_usable')->default(false);
            $table->boolean('is_weight')->default(true);
            $table->integer('price')->default(0);
            $table->integer('break_crystal')->default(0);
            $table->string('slot')->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('skill_lvl')->nullable();
            $table->integer('skill_exp')->nullable();
            $table->integer('gathering_time_seconds')->nullable();
            $table->integer('gathering_respawn_seconds')->nullable();
            $table->unsignedBigInteger('gathering_tool_share_item_id')->nullable();
            $table->string('rarity')->default('common');
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->integer('count')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('map_gathering_resources', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('max_active')->default(1);
            $table->integer('min_x');
            $table->integer('max_x');
            $table->integer('min_y');
            $table->integer('max_y');
            $table->timestamps();
            $table->unique(['map_id', 'share_item_id']);
        });
    }
}
