<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Rating;

use App\Modules\Rating\Application\UseCases\GetHomepageRankings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class HomepageRankingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Только SQLite :memory:, игровая база не используется.');
        }
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('lvl');
            $table->integer('exp');
        });
        Schema::create('clans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->integer('lvl');
            $table->integer('experience');
        });
        Schema::create('clan_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('clan_id');
        });
    }

    public function test_rankings_are_limited_sorted_and_load_clan_icons_without_owner_queries(): void
    {
        foreach (range(1, 12) as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Player'.$id]);
            DB::table('players')->insert(['id' => $id, 'user_id' => $id, 'lvl' => $id === 11 ? 12 : $id, 'exp' => $id === 11 ? 100 : 0]);
            DB::table('clans')->insert(['id' => $id, 'name' => 'Clan'.$id, 'lvl' => $id === 11 ? 12 : $id, 'experience' => $id === 11 ? 100 : 0, 'icon' => 'clan_icons/test.gif']);
        }
        DB::table('clan_members')->insert(['user_id' => 11, 'clan_id' => 11]);
        DB::enableQueryLog();
        $data = app(GetHomepageRankings::class)->execute();
        $this->assertCount(10, $data['topPlayers']);
        $this->assertCount(10, $data['topClans']);
        $this->assertEquals(11, $data['topPlayers']->first()->id);
        $this->assertEquals(12, $data['topPlayers'][1]->id);
        $this->assertEquals(11, $data['topClans']->first()->id);
        $this->assertEquals(12, $data['topClans'][1]->id);
        $html = view('news::partials.rankings', $data)->render();
        $this->assertStringContainsString('/info/u/11', $html);
        $this->assertStringContainsString('/clan-info/11', $html);
        $this->assertStringContainsString('/storage/clan_icons/test.gif', $html);
        $this->assertStringNotContainsString('Player1 [', $html);
        $this->assertStringNotContainsString('clan_info.php', $html);
        $this->assertLessThanOrEqual(5, count(DB::getQueryLog()));
    }

    public function test_empty_rankings_render(): void
    {
        $html = view('news::partials.rankings', app(GetHomepageRankings::class)->execute())->render();
        $this->assertStringContainsString('Игроков пока нет.', $html);
        $this->assertStringContainsString('Кланы пока не созданы.', $html);
    }
}
