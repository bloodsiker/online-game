<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonsterListFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('lvl')->default(1);
            $table->unsignedInteger('hp')->default(1);
            $table->unsignedInteger('min_dmg')->default(1);
            $table->unsignedInteger('max_dmg')->default(1);
            $table->unsignedInteger('exp')->default(0);
            $table->unsignedInteger('min_money')->default(0);
            $table->unsignedInteger('max_money')->default(0);
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });

        DB::table('monsters')->insert([
            ['id' => 11, 'name' => 'Вепрь', 'lvl' => 3],
            ['id' => 12, 'name' => 'Вепрь', 'lvl' => 1],
            ['id' => 13, 'name' => 'Альфа', 'lvl' => 5],
        ]);

        $this->withoutMiddleware(AdminMiddleware::class);
    }

    public function test_monster_filter_lists_unique_names_and_sorts_list(): void
    {
        $response = $this->get(route('admin.monsters'));
        $content = $response->getContent();

        $response->assertOk();
        $this->assertSame(1, substr_count($content, '<option value="Вепрь"'));

        $alphaPosition = strpos($content, route('admin.monster.info', 13));
        $levelOnePosition = strpos($content, route('admin.monster.info', 12));
        $levelThreePosition = strpos($content, route('admin.monster.info', 11));

        $this->assertIsInt($alphaPosition);
        $this->assertIsInt($levelOnePosition);
        $this->assertIsInt($levelThreePosition);
        $this->assertLessThan($levelOnePosition, $alphaPosition);
        $this->assertLessThan($levelThreePosition, $levelOnePosition);
    }

    public function test_monster_filter_returns_all_monsters_with_selected_name(): void
    {
        $response = $this->get(route('admin.monsters', ['monster_name' => 'Вепрь']));

        $response->assertOk();
        $response->assertSee('<option value="Вепрь" selected>', false);
        $response->assertSee(route('admin.monster.info', 11), false);
        $response->assertSee(route('admin.monster.info', 12), false);
        $response->assertDontSee(route('admin.monster.info', 13), false);
    }
}
