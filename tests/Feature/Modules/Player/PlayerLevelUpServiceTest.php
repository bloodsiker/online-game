<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Application\Listeners\RecalculatePlayerStats;
use App\Modules\Player\Domain\Events\PlayerChangeStat;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Player\Domain\Services\PlayerLevelUpService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlayerLevelUpServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('races', function (Blueprint $table): void {
            $table->id();
            $table->float('strength');
            $table->float('agility');
            $table->float('intuition');
            $table->float('wisdom');
            $table->float('intelligence');
            $table->float('endurance');
            $table->integer('free_stats');
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('race_id');
            $table->integer('lvl');
            $table->bigInteger('exp');
            $table->bigInteger('exp_up');
            $table->bigInteger('exp_diff');
            $table->float('strength');
            $table->float('agility');
            $table->float('intuition');
            $table->float('wisdom');
            $table->float('intelligence');
            $table->float('endurance');
            $table->integer('free_stats');
            $table->integer('hp_now')->default(10);
            $table->integer('hp_max')->default(10);
            $table->integer('mp_now')->default(10);
            $table->integer('mp_max')->default(10);
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->integer('lvl');
            $table->bigInteger('exp');
            $table->bigInteger('exp_diff');
            $table->timestamps();
        });

        DB::table('races')->insert([
            'id' => 1,
            'strength' => 2,
            'agility' => 3,
            'intuition' => 4,
            'wisdom' => 5,
            'intelligence' => 6,
            'endurance' => 7,
            'free_stats' => 8,
        ]);

        DB::table('players')->insert([
            'id' => 1,
            'user_id' => 1,
            'race_id' => 1,
            'lvl' => 1,
            'exp' => 0,
            'exp_up' => 100,
            'exp_diff' => 100,
            'strength' => 10,
            'agility' => 10,
            'intuition' => 10,
            'wisdom' => 10,
            'intelligence' => 10,
            'endurance' => 10,
            'free_stats' => 0,
        ]);

        DB::table('experiences')->insert([
            ['lvl' => 1, 'exp' => 0, 'exp_diff' => 100],
            ['lvl' => 2, 'exp' => 100, 'exp_diff' => 200],
            ['lvl' => 3, 'exp' => 300, 'exp_diff' => 300],
        ]);
    }

    public function test_it_raises_each_level_with_natural_stat_gains(): void
    {
        Event::forget(PlayerLeveledUp::class);
        Event::listen(PlayerLeveledUp::class, RecalculatePlayerStats::class);
        Event::fake([PlayerChangeStat::class]);

        $player = app(PlayerLevelUpService::class)->raiseToLevel(Player::findOrFail(1), 3);

        $this->assertSame(3, $player->lvl);
        $this->assertSame(300, $player->exp);
        $this->assertSame(600, $player->exp_up);
        $this->assertSame(300, $player->exp_diff);
        $this->assertSame(14.0, $player->strength);
        $this->assertSame(16.0, $player->agility);
        $this->assertSame(18.0, $player->intuition);
        $this->assertSame(20.0, $player->wisdom);
        $this->assertSame(22.0, $player->intelligence);
        $this->assertSame(24.0, $player->endurance);
        $this->assertSame(16, $player->free_stats);
        Event::assertDispatchedTimes(PlayerChangeStat::class, 2);
    }
}
