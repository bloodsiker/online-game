<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Monster;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonsterInfoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();
    }

    public function test_info_page_shows_each_habitat_map_only_once(): void
    {
        DB::table('maps')->insert([
            ['id' => 1, 'name' => 'Гранитный Перевал'],
            ['id' => 2, 'name' => 'Шепчущий Лес'],
        ]);
        DB::table('locations')->insert([
            ['id' => 10, 'map_id' => 1, 'name' => 'Каменный Хребет'],
            ['id' => 11, 'map_id' => 1, 'name' => 'Крутой Склон'],
            ['id' => 12, 'map_id' => 2, 'name' => 'Лесная Тропа'],
            ['id' => 13, 'map_id' => null, 'name' => 'Без карты'],
        ]);
        DB::table('monsters')->insert([
            'id' => 103,
            'name' => 'Каменный Голем',
            'lvl' => 10,
            'hp' => 100,
            'armor' => 5,
            'dodge' => 0,
            'critical' => 0,
            'min_dmg' => 10,
            'max_dmg' => 20,
            'aggression' => 0,
            'exp' => 50,
            'min_money' => 0,
            'max_money' => 0,
            'is_boss' => false,
        ]);
        DB::table('monster_on_locations')->insert([
            'id' => 5032,
            'monster_id' => 103,
            'location_id' => 10,
        ]);
        DB::table('location_has_monsters')->insert([
            ['location_id' => 10, 'monster_id' => 103, 'aggression' => null],
            ['location_id' => 11, 'monster_id' => 103, 'aggression' => null],
            ['location_id' => 12, 'monster_id' => 103, 'aggression' => null],
            ['location_id' => 13, 'monster_id' => 103, 'aggression' => null],
        ]);

        $response = $this->get(route('info.monster', ['id' => 5032]));
        $content = $response->getContent();

        $response->assertOk();
        $response->assertSeeText('Карты обитания');
        $response->assertSeeText('Гранитный Перевал, Шепчущий Лес');
        $this->assertSame(1, substr_count($content, 'Гранитный Перевал'));
        $this->assertSame(1, substr_count($content, 'Шепчущий Лес'));
        $response->assertDontSeeText('Без карты');
    }

    private function createTables(): void
    {
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id')->nullable();
            $table->string('name');
        });

        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('lvl');
            $table->unsignedInteger('hp');
            $table->unsignedInteger('armor');
            $table->unsignedInteger('dodge');
            $table->unsignedInteger('critical');
            $table->double('min_dmg');
            $table->double('max_dmg');
            $table->unsignedInteger('aggression');
            $table->unsignedInteger('exp');
            $table->unsignedInteger('min_money');
            $table->unsignedInteger('max_money');
            $table->boolean('is_boss');
            $table->timestamps();
        });

        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('location_id')->nullable();
        });

        Schema::create('location_has_monsters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('monster_id');
            $table->unsignedInteger('aggression')->nullable();
        });

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('monster_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('share_item_id');
            $table->double('drop_chance')->default(0);
            $table->unsignedInteger('min_count')->default(1);
            $table->unsignedInteger('max_count')->default(1);
        });
    }
}
