<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\UseCases\GetHomepageBossBattles;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class HomepageBossBattlesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->integer('status');
            $table->timestamps();
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('location_monster_id')->nullable();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('image')->nullable();
            $table->integer('lvl');
            $table->boolean('is_boss');
        });
    }

    public function test_latest_ten_boss_battles_are_distinct_sorted_localized_and_loaded_in_batches(): void
    {
        foreach ([1 => true, 2 => false, 3 => true] as $id => $boss) {
            DB::table('monsters')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Монстр'.$id, 'uk' => 'Істота'.$id]),
                'lvl' => 35, 'is_boss' => $boss, 'image' => 'monsters/boss.webp']);
            DB::table('monster_on_locations')->insert(['id' => $id, 'monster_id' => $id]);
        }
        DB::table('monster_on_locations')->insert(['id' => 4, 'monster_id' => 1]);
        foreach (range(1, 16) as $id) {
            DB::table('battles')->insert(['id' => $id, 'status' => $id === 2 ? 1 : 0,
                'created_at' => $id === 2 ? now() : now()->subMinutes(20 - $id), 'updated_at' => now()]);
            if ($id < 16) {
                DB::table('battle_details')->insert(['battle_id' => $id, 'location_monster_id' => match ($id) {
                    14 => 2, 15 => null, default => 1
                }]);
            }
        }
        DB::table('battles')->where('id', 12)->update(['created_at' => DB::table('battles')->where('id', 13)->value('created_at')]);
        foreach ([2, 3, 4] as $spawn) {
            DB::table('battle_details')->insert(['battle_id' => 13, 'location_monster_id' => $spawn]);
        }

        DB::enableQueryLog();
        $battles = app(GetHomepageBossBattles::class)->execute();
        $this->assertSame([2, 13, 12, 11, 10, 9, 8, 7, 6, 5], $battles->modelKeys());
        $this->assertTrue($battles->first()->status->isActive());
        $this->assertTrue($battles[1]->status->isFinish());
        $this->assertCount(3, $battles[1]->detailsWithMonsters);
        $html = view('news::partials.boss-battles', ['homepageBossBattles' => $battles])->render();
        $this->assertSame(10, substr_count($html, 'class="b-discuss__element homepage-boss-battles__battle"'));
        $this->assertStringContainsString('Монстр1 [35], Монстр3 [35]', $html);
        $this->assertStringNotContainsString('Монстр2', $html);
        $this->assertStringContainsString(route('fight.log', ['id' => 13]), $html);
        $this->assertLessThanOrEqual(4, count(DB::getQueryLog()));
        DB::disableQueryLog();
        app()->setLocale('uk');
        $html = view('news::partials.boss-battles', ['homepageBossBattles' => $battles])->render();
        $this->assertStringContainsString('Істота1 [35], Істота3 [35]', $html);
        $this->assertStringContainsString('Бої з босами', $html);
        $this->assertStringContainsString('Триває бій', $html);
        $this->assertSame(16, DB::table('battles')->count());
    }

    public function test_empty_battle_history_has_a_readable_placeholder(): void
    {
        $html = view('news::partials.boss-battles', ['homepageBossBattles' => app(GetHomepageBossBattles::class)->execute()])->render();
        $this->assertStringContainsString('Боёв с боссами пока нет.', $html);
        $this->assertStringNotContainsString('href=', $html);
    }
}
