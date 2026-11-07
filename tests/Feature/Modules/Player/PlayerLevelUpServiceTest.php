<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\AttackService;
use App\Modules\Player\Application\Listeners\RecalculatePlayerStats;
use App\Modules\Player\Domain\Events\PlayerChangeStat;
use App\Modules\Player\Domain\Events\PlayerLevelChanged;
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
        Event::forget(PlayerLevelChanged::class);
        Event::listen(PlayerLevelChanged::class, RecalculatePlayerStats::class);
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

    private function useSynchronousProgression(array &$notifications): void
    {
        Event::forget(PlayerLeveledUp::class);
        Event::listen(PlayerLeveledUp::class, static function (PlayerLeveledUp $event) use (&$notifications): void {
            $notifications[] = ['level' => (int) $event->player->lvl, 'threshold' => (int) $event->player->exp_up];
        });
        Event::fake([PlayerChangeStat::class]);
    }

    public function test_battle_level_gain_updates_threshold_before_commit_and_keeps_notification_snapshots(): void
    {
        $notifications = [];
        $this->useSynchronousProgression($notifications);
        DB::beginTransaction();
        $player = Player::query()->lockForUpdate()->findOrFail(1);
        $player->exp = 350;
        $result = new AttackResultDTO;

        app(AttackService::class)->checkLevelUp($player, $result);

        $this->assertSame(3, (int) $player->lvl);
        $this->assertSame(600, (int) $player->exp_up);
        $this->assertSame(350, (int) $player->exp);
        $this->assertSame(16, (int) $player->free_stats);
        $this->assertSame([], $notifications);
        $this->assertStringContainsString('<b>2</b>', $result->getSideLog());
        $this->assertStringContainsString('<b>3</b>', $result->getSideLog());
        DB::commit();

        $this->assertSame([['level' => 2, 'threshold' => 300], ['level' => 3, 'threshold' => 600]], $notifications);
        Event::assertDispatchedTimes(PlayerChangeStat::class, 2);
    }

    public function test_rolled_back_level_gain_restores_experience_stats_and_sends_no_notifications(): void
    {
        $notifications = [];
        $this->useSynchronousProgression($notifications);
        DB::beginTransaction();
        $player = Player::query()->lockForUpdate()->findOrFail(1);
        $player->exp = 100;
        app(PlayerLevelUpService::class)->raiseFromExperience($player);
        $this->assertSame(2, (int) $player->lvl);
        $this->assertSame(300, (int) $player->exp_up);
        DB::rollBack();

        $stored = Player::findOrFail(1);
        $this->assertSame(1, (int) $stored->lvl);
        $this->assertSame(0, (int) $stored->exp);
        $this->assertSame(100, (int) $stored->exp_up);
        $this->assertSame(0, (int) $stored->free_stats);
        $this->assertSame([], $notifications);
    }

    public function test_maximum_configured_level_does_not_loop_or_consume_excess_experience(): void
    {
        $notifications = [];
        $this->useSynchronousProgression($notifications);
        DB::transaction(function (): void {
            $player = Player::query()->lockForUpdate()->findOrFail(1);
            $player->exp = 10000;
            app(PlayerLevelUpService::class)->raiseFromExperience($player);
            $this->assertSame(3, (int) $player->lvl);
            $this->assertSame(10000, (int) $player->exp);
            app(PlayerLevelUpService::class)->raiseFromExperience($player);
            $this->assertSame(16, (int) $player->free_stats);
        });
        $this->assertCount(2, $notifications);
    }
}
