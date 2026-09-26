<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Influence;

use App\Modules\Influence\Application\UseCases\GetMapInfluenceDetails;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class GetMapInfluenceDetailsTest extends TestCase
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
            $table->string('slug')->nullable();
        });
        Schema::create('map_influences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('influence');
        });
        Schema::create('influence_medals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('rating_points')->default(0);
        });
        Schema::create('map_influence_levels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedSmallInteger('level');
            $table->string('name');
            $table->unsignedBigInteger('required_influence');
            $table->unsignedBigInteger('influence_medal_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
        Schema::create('influence_medal_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('influence_medal_id');
            $table->string('stat_type');
            $table->decimal('value', 10, 3);
            $table->boolean('is_percent')->default(false);
        });
        Schema::create('map_influence_level_bonuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_influence_level_id');
            $table->string('bonus_type');
            $table->decimal('value', 8, 3);
        });
        Schema::create('map_influence_level_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_influence_level_id');
            $table->string('reward_type');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->json('config')->nullable();
        });
    }

    public function test_it_builds_and_renders_full_influence_progression(): void
    {
        DB::table('maps')->insert(['id' => 1, 'name' => 'Дартронг', 'slug' => 'dartrong']);
        DB::table('map_influences')->insert(['map_id' => 1, 'user_id' => 7, 'influence' => 40]);
        DB::table('influence_medals')->insert([
            'id' => 1,
            'map_id' => 1,
            'name' => 'Знак Дартронга',
            'description' => 'Первая медаль территории.',
            'rating_points' => 5,
        ]);
        DB::table('map_influence_levels')->insert([
            ['id' => 1, 'map_id' => 1, 'level' => 1, 'name' => 'Знакомый', 'required_influence' => 0, 'influence_medal_id' => 1],
            ['id' => 2, 'map_id' => 1, 'level' => 2, 'name' => 'Союзник', 'required_influence' => 100, 'influence_medal_id' => null],
        ]);
        DB::table('influence_medal_stats')->insert([
            'influence_medal_id' => 1,
            'stat_type' => 'armor',
            'value' => 2,
        ]);
        DB::table('map_influence_level_bonuses')->insert([
            'map_influence_level_id' => 1,
            'bonus_type' => 'gathering_speed_percent',
            'value' => 3,
        ]);
        DB::table('map_influence_level_rewards')->insert([
            'map_influence_level_id' => 2,
            'reward_type' => 'money',
            'amount' => 1000,
        ]);

        $page = app(GetMapInfluenceDetails::class)->execute(7, Map::query()->findOrFail(1));

        $this->assertSame(40, $page['influence']);
        $this->assertSame(40, $page['progressPercent']);
        $this->assertSame('Знакомый', $page['currentLevel']->name);
        $this->assertSame('Союзник', $page['nextLevel']->name);
        $this->assertCount(2, $page['levels']);

        $html = view('player::influence-details', compact('page'))->render();
        $this->assertStringContainsString('Шкала уровней влияния', $html);
        $this->assertStringContainsString('Скорость добычи, %', $html);
        $this->assertStringContainsString('Броня', $html);
        $this->assertStringContainsString('Монеты', $html);
    }
}
