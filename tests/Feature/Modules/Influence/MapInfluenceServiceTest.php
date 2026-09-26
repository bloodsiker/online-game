<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Influence;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Influence\Domain\Events\MapInfluenceAwarded;
use App\Modules\Influence\Domain\Services\MapInfluenceBonusService;
use App\Modules\Influence\Domain\Services\MapInfluenceRequirementService;
use App\Modules\Influence\Domain\Services\MapInfluenceService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

final class MapInfluenceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();
    }

    public function test_award_is_idempotent_and_grants_crossed_level_rewards_and_medal(): void
    {
        Event::fake([MapInfluenceAwarded::class]);

        DB::table('users')->insert(['id' => 1, 'player_id' => 1, 'money' => 0, 'diamond' => 0]);
        DB::table('maps')->insert(['id' => 1, 'name' => 'Дартронг']);
        DB::table('influence_medals')->insert(['id' => 1, 'map_id' => 1, 'name' => 'Друг Дартронга', 'rating_points' => 10]);
        DB::table('map_influence_levels')->insert([
            'id' => 1,
            'map_id' => 1,
            'level' => 1,
            'name' => 'Известный',
            'required_influence' => 10,
            'influence_medal_id' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        DB::table('map_influence_level_rewards')->insert([
            'id' => 1,
            'map_influence_level_id' => 1,
            'reward_type' => 'money',
            'amount' => 100,
        ]);

        $stats = Mockery::mock(PlayerStatService::class);
        $stats->shouldReceive('invalidate')->once()->with(1);
        $service = new MapInfluenceService(
            Mockery::mock(BackpackService::class),
            $stats,
            new MapInfluenceBonusService,
            new MapInfluenceRequirementService,
        );
        $user = User::query()->without('player')->findOrFail(1);

        $first = $service->award($user, 1, 15, 'test', 1, 'test-award-1');
        $second = $service->award($user, 1, 15, 'test', 1, 'test-award-1');

        $this->assertSame(15, $first->total);
        $this->assertSame(['Известный'], $first->unlockedLevels);
        $this->assertSame(0, $second->awarded);
        $this->assertSame(15, $second->total);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('player_influence_medals', ['user_id' => 1, 'influence_medal_id' => 1]);
        $this->assertDatabaseCount('map_influence_transactions', 1);
        $this->assertDatabaseCount('player_map_influence_level_rewards', 1);
        Event::assertDispatchedTimes(MapInfluenceAwarded::class, 1);
        Event::assertDispatched(
            MapInfluenceAwarded::class,
            fn (MapInfluenceAwarded $event): bool => $event->user->is($user)
                && $event->mapName === 'Дартронг'
                && $event->amount === 15
                && $event->total === 15,
        );
    }

    public function test_only_highest_unlocked_level_defines_local_bonuses(): void
    {
        DB::table('users')->insert(['id' => 1, 'player_id' => 1, 'money' => 0, 'diamond' => 0]);
        DB::table('maps')->insert(['id' => 1, 'name' => 'Дартронг']);
        DB::table('map_influences')->insert(['map_id' => 1, 'user_id' => 1, 'influence' => 500]);
        DB::table('map_influence_levels')->insert([
            ['id' => 1, 'map_id' => 1, 'level' => 1, 'name' => 'Первый', 'required_influence' => 100, 'is_active' => true, 'sort_order' => 1],
            ['id' => 2, 'map_id' => 1, 'level' => 2, 'name' => 'Второй', 'required_influence' => 500, 'is_active' => true, 'sort_order' => 2],
        ]);
        DB::table('map_influence_level_bonuses')->insert([
            ['map_influence_level_id' => 1, 'bonus_type' => 'gathering_speed_percent', 'value' => 2],
            ['map_influence_level_id' => 2, 'bonus_type' => 'gathering_speed_percent', 'value' => 5],
            ['map_influence_level_id' => 2, 'bonus_type' => 'monster_money_percent', 'value' => 3],
        ]);

        $bonuses = (new MapInfluenceBonusService)->for(1, 1);

        $this->assertSame(5.0, $bonuses->gatheringSpeedPercent);
        $this->assertSame(3.0, $bonuses->monsterMoneyPercent);
        $this->assertSame(0.0, $bonuses->bonusResourceChancePercent);
    }

    private function createTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('money')->default(0);
            $table->unsignedBigInteger('diamond')->default(0);
            $table->timestamps();
        });
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('map_influences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('influence')->default(0);
            $table->timestamps();
            $table->unique(['map_id', 'user_id']);
        });
        Schema::create('influence_medals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('rating_points')->default(0);
            $table->timestamps();
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
            $table->timestamps();
        });
        Schema::create('map_influence_level_bonuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_influence_level_id');
            $table->string('bonus_type');
            $table->decimal('value', 8, 3);
            $table->timestamps();
        });
        Schema::create('map_influence_level_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_influence_level_id');
            $table->string('reward_type');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->json('config')->nullable();
            $table->timestamps();
        });
        Schema::create('player_influence_medals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('influence_medal_id');
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->unique(['user_id', 'influence_medal_id']);
        });
        Schema::create('player_map_influence_level_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('map_influence_level_reward_id');
            $table->timestamp('granted_at');
            $table->timestamps();
            $table->unique(['user_id', 'map_influence_level_reward_id']);
        });
        Schema::create('map_influence_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('map_id');
            $table->bigInteger('amount');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
}
