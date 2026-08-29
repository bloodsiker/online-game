<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Location;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\Item\Domain\Services\ItemRequirementService;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Location\Application\Jobs\BroadcastGatheringMapUpdate;
use App\Modules\Location\Domain\Events\GatheringMapUpdated;
use App\Modules\Location\Domain\Services\GatheringService;
use App\Modules\Player\Application\Services\HotbarService;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Structure\Workshop\Application\UseCases\CraftProfessionItem;
use App\Modules\Structure\Workshop\Application\UseCases\LearnRecipe;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PeacefulProfessionFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();
        $this->seedPlayerAndProfession();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_resource_is_reserved_completed_and_respawned_with_profession_experience(): void
    {
        Carbon::setTestNow('2026-08-29 12:00:00');
        $this->seedGatheringResource();

        $backpack = Mockery::mock(BackpackService::class);
        $backpack->shouldReceive('addItemByShareItem')->once()->andReturn(new Backpack);
        $service = new GatheringService($backpack);
        $user = User::query()->findOrFail(1);

        $state = $service->state($user);
        $this->assertCount(1, $state['nodes']);
        $this->assertSame('/herb-transparent.png', $state['nodes'][0]['image']);
        $nodeId = $state['nodes'][0]['id'];
        $previousPosition = DB::table('gathering_nodes')->where('id', $nodeId)->first(['x_percent', 'y_percent']);

        $started = $service->start($user, $nodeId);
        $this->assertTrue($started->ok);
        $this->assertDatabaseHas('gathering_attempts', ['player_id' => 1, 'gathering_node_id' => $nodeId]);

        Carbon::setTestNow('2026-08-29 12:00:05');
        $completed = $service->complete($user);

        $this->assertTrue($completed->ok);
        $this->assertSame('/herb-transparent.png', $completed->data['reward']['image']);
        $this->assertDatabaseMissing('gathering_attempts', ['player_id' => 1]);
        $this->assertDatabaseHas('player_skills', ['player_id' => 1, 'skill_id' => 10, 'exp' => 7]);
        $this->assertNotNull(DB::table('gathering_nodes')->where('id', $nodeId)->value('respawn_at'));
        $newPosition = DB::table('gathering_nodes')->where('id', $nodeId)->first(['x_percent', 'y_percent']);
        $this->assertGreaterThanOrEqual(18, hypot((float) $newPosition->x_percent - (float) $previousPosition->x_percent, (float) $newPosition->y_percent - (float) $previousPosition->y_percent));
    }

    public function test_gathering_changes_are_broadcast_and_delayed_updates_are_queued(): void
    {
        Carbon::setTestNow('2026-08-29 12:00:00');
        Event::fake([GatheringMapUpdated::class]);
        Queue::fake([BroadcastGatheringMapUpdate::class]);
        $this->seedGatheringResource();

        $backpack = Mockery::mock(BackpackService::class);
        $backpack->shouldReceive('addItemByShareItem')->once()->andReturn(new Backpack);
        $service = new GatheringService($backpack);
        $user = User::query()->findOrFail(1);
        $nodeId = $service->state($user)['nodes'][0]['id'];

        $this->assertTrue($service->start($user, $nodeId)->ok);
        Event::assertDispatched(
            GatheringMapUpdated::class,
            fn (GatheringMapUpdated $event): bool => $event->mapId === 1
                && $event->nodeId === $nodeId
                && $event->reason === 'attempt-started',
        );
        Queue::assertPushed(
            BroadcastGatheringMapUpdate::class,
            fn (BroadcastGatheringMapUpdate $job): bool => $job->reason === 'attempt-expired',
        );

        Carbon::setTestNow('2026-08-29 12:00:05');
        $this->assertTrue($service->complete($user)->ok);
        Event::assertDispatched(
            GatheringMapUpdated::class,
            fn (GatheringMapUpdated $event): bool => $event->reason === 'resource-collected',
        );
        Queue::assertPushed(
            BroadcastGatheringMapUpdate::class,
            fn (BroadcastGatheringMapUpdate $job): bool => $job->reason === 'resource-respawned',
        );
    }

    public function test_required_tool_is_accepted_in_either_hand(): void
    {
        $this->seedGatheringResource();

        $backpack = Mockery::mock(BackpackService::class);
        $service = new GatheringService($backpack);

        $rightHandState = $service->state(User::query()->findOrFail(1));
        $this->assertTrue($rightHandState['nodes'][0]['canGather']);

        DB::table('player_equipments')->where('player_id', 1)->update([
            'hand_left' => 200,
            'hand_right' => null,
        ]);

        $leftHandState = $service->state(User::query()->findOrFail(1));
        $this->assertTrue($leftHandState['nodes'][0]['canGather']);
    }

    public function test_multiple_players_can_gather_one_resource_but_only_first_to_complete_receives_it(): void
    {
        Carbon::setTestNow('2026-08-29 12:00:00');
        $this->seedGatheringResource();
        $this->seedSecondGatherer();

        $backpack = Mockery::mock(BackpackService::class);
        $backpack->shouldReceive('addItemByShareItem')->once()->andReturn(new Backpack);
        $service = new GatheringService($backpack);
        $firstPlayer = User::query()->findOrFail(1);
        $secondPlayer = User::query()->findOrFail(2);
        $nodeId = $service->state($firstPlayer)['nodes'][0]['id'];

        $this->assertTrue($service->start($firstPlayer, $nodeId)->ok);
        $secondPlayerState = $service->state($secondPlayer);
        $this->assertTrue($secondPlayerState['nodes'][0]['busy']);
        $this->assertSame(1, $secondPlayerState['nodes'][0]['gatheringPlayersCount']);
        $this->assertTrue($secondPlayerState['nodes'][0]['canGather']);
        $this->assertTrue($service->start($secondPlayer, $nodeId)->ok);
        $this->assertDatabaseCount('gathering_attempts', 2);

        Carbon::setTestNow('2026-08-29 12:00:05');
        $this->assertTrue($service->complete($firstPlayer)->ok);

        $secondResult = $service->complete($secondPlayer);
        $this->assertFalse($secondResult->ok);
        $this->assertSame(200, $secondResult->httpCode);
        $this->assertSame('Добыча не удалась: ресурс уже собран другим игроком.', $secondResult->message);
        $this->assertDatabaseMissing('gathering_attempts', ['player_id' => 2]);
        $this->assertDatabaseHas('player_skills', ['player_id' => 1, 'skill_id' => 10, 'exp' => 7]);
        $this->assertDatabaseHas('player_skills', ['player_id' => 2, 'skill_id' => 10, 'exp' => 0]);
    }

    public function test_second_tool_cannot_be_equipped_when_another_tool_is_in_hand(): void
    {
        $this->seedGatheringResource();
        DB::table('items')->insert(['id' => 201, 'share_item_id' => 21]);
        DB::table('backpacks')->insert([
            'user_id' => 1,
            'item_id' => 201,
            'equipped' => false,
            'count' => 1,
        ]);

        $requirements = Mockery::mock(ItemRequirementService::class);
        $requirements->shouldReceive('check')->once()->andReturnNull();

        $service = new ItemService(
            Mockery::mock(BackpackService::class),
            Mockery::mock(HotbarService::class),
            $requirements,
            Mockery::mock(QuestProgressService::class),
            Mockery::mock(ItemActionLogger::class),
        );

        $error = $service->equip(User::query()->findOrFail(1), 201);

        $this->assertSame('В руках уже находится инструмент.', $error);
        $this->assertDatabaseHas('player_equipments', [
            'player_id' => 1,
            'hand_left' => null,
            'hand_right' => 200,
        ]);
        $this->assertDatabaseHas('backpacks', [
            'item_id' => 201,
            'equipped' => false,
        ]);
    }

    public function test_stale_active_battle_on_another_location_does_not_block_gathering(): void
    {
        $this->seedGatheringResource();
        DB::table('battles')->insert(['id' => 50, 'location_id' => 999, 'status' => 1]);
        DB::table('battle_details')->insert([
            'battle_id' => 50,
            'user_id' => 1,
            'status' => 1,
        ]);

        $backpack = Mockery::mock(BackpackService::class);
        $service = new GatheringService($backpack);

        $stateOutsideBattleLocation = $service->state(User::query()->findOrFail(1));
        $this->assertTrue($stateOutsideBattleLocation['enabled']);

        DB::table('battles')->where('id', 50)->update(['location_id' => 1]);

        $stateOnBattleLocation = $service->state(User::query()->findOrFail(1));
        $this->assertFalse($stateOnBattleLocation['enabled']);
        $this->assertSame('Во время боя добыча ресурсов недоступна.', $stateOnBattleLocation['message']);
    }

    public function test_recipe_book_is_consumed_once_and_learned_recipe_crafts_one_item_without_failure(): void
    {
        $this->seedRecipe();
        $user = User::query()->findOrFail(1);

        $learned = (new LearnRecipe)->execute($user, 30);

        $this->assertTrue($learned->ok);
        $this->assertDatabaseHas('player_recipes', ['player_id' => 1, 'share_recipe_id' => 1]);
        $this->assertDatabaseMissing('backpacks', ['item_id' => 301]);

        $backpack = Mockery::mock(BackpackService::class);
        $backpack->shouldReceive('addItemByShareItem')->once()->andReturn(new Backpack);
        $crafted = (new CraftProfessionItem($backpack))->execute($user, 1, 1);

        $this->assertTrue($crafted->ok);
        $this->assertDatabaseMissing('backpacks', ['item_id' => 401]);
    }

    private function seedPlayerAndProfession(): void
    {
        DB::table('races')->insert(['id' => 1, 'name' => 'Человек']);
        DB::table('players')->insert(['id' => 1, 'user_id' => 1, 'race_id' => 1, 'hp_now' => 100]);
        DB::table('maps')->insert(['id' => 1, 'name' => 'Заросшая дорога']);
        DB::table('locations')->insert(['id' => 1, 'map_id' => 1, 'name' => 'Поляна']);
        DB::table('users')->insert(['id' => 1, 'player_id' => 1, 'location_id' => 1, 'name' => 'Игрок', 'email' => 'player@test.local', 'password' => 'x']);
        DB::table('skills')->insert(['id' => 10, 'name' => 'Травник', 'type' => 'peaceful']);
        DB::table('skill_level_requirements')->insert([
            ['skill_id' => 10, 'lvl' => 1, 'exp_required' => 100, 'exp_diff' => 100],
            ['skill_id' => 10, 'lvl' => 2, 'exp_required' => 400, 'exp_diff' => 300],
        ]);
    }

    private function seedGatheringResource(): void
    {
        DB::table('share_items')->insert(['id' => 20, 'type' => 'tool', 'name' => 'Серп', 'image' => '/serp.png', 'slot' => 'hand', 'rarity' => 'common']);
        DB::table('share_items')->insert(['id' => 21, 'type' => 'tool', 'name' => 'Кирка', 'image' => '/pick.png', 'slot' => 'hand', 'rarity' => 'common']);
        DB::table('share_items')->insert(['id' => 10, 'type' => 'resource', 'name' => 'Лечебная трава', 'image' => '/herb.png', 'transparent_image' => '/herb-transparent.png', 'rarity' => 'common', 'skill_id' => 10, 'skill_lvl' => 1, 'skill_exp' => 7, 'gathering_time_seconds' => 5, 'gathering_respawn_seconds' => 30, 'gathering_tool_share_item_id' => 20]);
        DB::table('items')->insert(['id' => 200, 'share_item_id' => 20]);
        DB::table('player_equipments')->insert(['player_id' => 1, 'hand_left' => null, 'hand_right' => 200]);
        DB::table('map_gathering_resources')->insert(['id' => 1, 'map_id' => 1, 'share_item_id' => 10, 'max_active' => 1, 'min_x' => 10, 'max_x' => 90, 'min_y' => 20, 'max_y' => 70]);
    }

    private function seedSecondGatherer(): void
    {
        DB::table('players')->insert(['id' => 2, 'user_id' => 2, 'race_id' => 1, 'hp_now' => 100]);
        DB::table('users')->insert(['id' => 2, 'player_id' => 2, 'location_id' => 1, 'name' => 'Другой игрок', 'email' => 'player2@test.local', 'password' => 'x']);
        DB::table('items')->insert(['id' => 202, 'share_item_id' => 20]);
        DB::table('player_equipments')->insert(['player_id' => 2, 'hand_left' => null, 'hand_right' => 202]);
    }

    private function seedRecipe(): void
    {
        DB::table('share_items')->insert(['id' => 30, 'type' => 'recipe', 'name' => 'Рецепт настоя', 'image' => '/recipe.png', 'rarity' => 'common', 'skill_id' => 10, 'skill_lvl' => 1]);
        DB::table('share_items')->insert(['id' => 31, 'type' => 'potion', 'name' => 'Травяной настой', 'image' => '/potion.png', 'rarity' => 'common']);
        DB::table('share_items')->insert(['id' => 32, 'type' => 'resource', 'name' => 'Трава', 'image' => '/herb.png', 'rarity' => 'common']);
        DB::table('share_recipes')->insert(['id' => 1, 'share_item_id' => 30, 'kraft_item_id' => 31, 'percent' => 1]);
        DB::table('share_recipe_has_items')->insert(['share_recipe_id' => 1, 'share_item_id' => 32, 'count' => 2]);
        DB::table('items')->insert([
            ['id' => 301, 'share_item_id' => 30],
            ['id' => 401, 'share_item_id' => 32],
        ]);
        DB::table('backpacks')->insert([
            ['user_id' => 1, 'item_id' => 301, 'equipped' => 0, 'count' => 1],
            ['user_id' => 1, 'item_id' => 401, 'equipped' => 0, 'count' => 2],
        ]);
        DB::table('player_skills')->insert(['player_id' => 1, 'skill_id' => 10, 'lvl' => 1, 'exp' => 0, 'exp_up' => 100, 'exp_diff' => 100]);
        DB::table('structures')->insert(['id' => 1, 'type' => 'workshop', 'name' => 'Мастерской дом', 'location_id' => 1]);
    }

    private function createTables(): void
    {
        Schema::create('races', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('race_id');
            $table->integer('hp_now')->default(100);
            $table->timestamps();
        });
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id')->nullable();
            $table->unsignedBigInteger('dungeon_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('location_id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('skill_level_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('skill_id');
            $table->unsignedInteger('lvl');
            $table->unsignedInteger('exp_required');
            $table->unsignedInteger('exp_diff');
        });
        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->integer('lvl')->default(1);
            $table->integer('exp')->default(0);
            $table->integer('exp_up')->default(100);
            $table->integer('exp_diff')->default(100);
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('transparent_image')->nullable();
            $table->string('rarity')->default('common');
            $table->string('slot')->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('skill_lvl')->nullable();
            $table->integer('skill_exp')->nullable();
            $table->integer('gathering_time_seconds')->nullable();
            $table->integer('gathering_respawn_seconds')->nullable();
            $table->unsignedBigInteger('gathering_tool_share_item_id')->nullable();
            $table->boolean('is_stackable')->default(true);
            $table->integer('count_use')->default(0);
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count_use')->default(0);
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
        Schema::create('player_equipments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('hand_left')->nullable();
            $table->unsignedBigInteger('hand_right')->nullable();
            $table->timestamps();
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
            $table->unsignedBigInteger('player_id')->unique();
            $table->unsignedBigInteger('gathering_node_id');
            $table->unsignedBigInteger('location_id');
            $table->timestamp('completes_at');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->integer('percent')->default(100);
            $table->timestamps();
        });
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count');
            $table->timestamps();
        });
        Schema::create('player_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('share_recipe_id');
            $table->timestamps();
            $table->unique(['player_id', 'share_recipe_id']);
        });
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamps();
        });
    }
}
