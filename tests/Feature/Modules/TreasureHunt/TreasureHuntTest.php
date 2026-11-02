<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\TreasureHunt;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\ItemAssembly\Application\Requests\SaveItemAssemblyRequest;
use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\Player\Application\Services\HotbarService;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSlot;
use App\Modules\Player\Presentation\Http\HotbarController;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\TreasureHunt\Application\Requests\SaveTreasureMapRequest;
use App\Modules\TreasureHunt\Application\Services\AssignTreasureDestination;
use App\Modules\TreasureHunt\Application\Services\ManageTreasureMaps;
use App\Modules\TreasureHunt\Application\Services\TreasureHuntService;
use App\Modules\TreasureHunt\Application\Services\TreasureSearchRandom;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureHunt;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use App\Modules\TreasureHunt\Presentation\Http\Admin\TreasureMapController;
use App\Modules\TreasureHunt\Presentation\Http\TreasureHuntController;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class TreasureHuntTest extends TestCase
{
    private User $user;

    private bool $success = true;

    private bool $chatFails = false;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->freezeTime();
        foreach (['maps', 'reputations'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->json('name')->nullable();
            });
        }
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedBigInteger('dungeon_id')->nullable();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('player_id');
            $table->timestamps();
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('hp_now')->default(100);
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->string('rarity')->default('common');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_slot_usable')->default(true);
            $table->integer('count_use')->default(0);
            $table->boolean('is_give')->default(false);
            $table->boolean('is_auction_sellable')->default(false);
            $table->boolean('is_clan_warehouse_allowed')->default(false);
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('player_trade_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->timestamp('expires_at');
            $table->unsignedBigInteger('initiator_user_id');
            $table->unsignedBigInteger('target_user_id');
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->timestamp('expires_at')->nullable();
            foreach (['upgrade_lvl', 'upgrade_pity', 'upgrade_fail_streak', 'socket_count', 'rune_slot_count', 'additional_attack', 'count_use', 'is_open'] as $field) {
                $table->integer($field)->default(0);
            }
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('count')->default(1);
            $table->integer('equipped')->default(0);
            $table->timestamps();
        });
        Schema::create('player_slots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedInteger('slot_number');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->timestamps();
        });
        Schema::create('reputation_tiers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reputation_id');
            $table->integer('min_points');
        });
        Schema::create('player_reputations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('reputation_id');
            $table->integer('points')->default(0);
            $table->timestamps();
        });
        Schema::create('share_item_lock_configs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('lock_required_skill');
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->integer('status');
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('battle_id');
            $table->integer('status');
        });
        foreach (['gathering_attempts', 'lockpicking_attempts'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('player_id');
                $table->timestamp('expires_at');
            });
        }
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('channel');
            $table->string('type');
            $table->string('message');
            $table->string('translation_key')->nullable();
            $table->json('translation_params')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_31_000001_create_treasure_hunts.php'))->up();
        (require database_path('migrations/2026_10_31_000002_randomize_treasure_map_territories.php'))->up();
        DB::table('maps')->insert(['id' => 1]);
        DB::table('locations')->insert([['id' => 6, 'map_id' => 1], ['id' => 7, 'map_id' => 1]]);
        DB::table('users')->insert(['id' => 1, 'player_id' => 1, 'location_id' => 6]);
        DB::table('players')->insert(['id' => 1, 'user_id' => 1]);
        DB::table('reputations')->insert(['id' => 1, 'name' => '{"ru":"Кладоискатель","uk":"Шукач скарбів"}']);
        DB::table('reputation_tiers')->insert(['id' => 1, 'reputation_id' => 1, 'min_points' => 0]);
        DB::table('share_items')->insert([
            ['id' => 1, 'name' => '{"ru":"Карта","uk":"Карта"}', 'type' => 'treasure_map'],
            ['id' => 2, 'name' => '{"ru":"Шкатулка","uk":"Скринька"}', 'type' => 'chest'],
        ]);
        DB::table('share_item_lock_configs')->insert(['share_item_id' => 2, 'lock_required_skill' => 25]);
        DB::table('items')->insert(['id' => 1, 'share_item_id' => 1]);
        $config = TreasureMapConfig::create(['share_item_id' => 1, 'reward_share_item_id' => 2, 'reputation_id' => 1, 'map_id' => 1, 'clue' => ['ru' => 'У фонтана'], 'success_chance_percent' => 70, 'search_duration_seconds' => 12]);
        $config->locations()->sync([6]);
        $this->user = User::findOrFail(1);
        $random = \Mockery::mock(TreasureSearchRandom::class);
        $random->shouldReceive('territory')->andReturn(1);
        $random->shouldReceive('location')->andReturn(6);
        $random->shouldReceive('succeeds')->andReturnUsing(fn (float $chance) => $this->success);
        $this->app->instance(TreasureSearchRandom::class, $random);
        Backpack::create(['user_id' => 1, 'item_id' => 1]);
        $reputation = \Mockery::mock(ReputationService::class);
        $reputation->shouldReceive('addPoints')->andReturnUsing(function ($player, $rep, int $amount, bool $touchCooldown): PlayerReputation {
            $this->assertFalse($touchCooldown);
            $points = PlayerReputation::firstOrCreate(['player_id' => $player->id, 'reputation_id' => $rep->id], ['points' => 0]);
            $points->increment('points', $amount);

            return $points;
        });
        $this->app->instance(ReputationService::class, $reputation);
        $chat = \Mockery::mock(ChatMessageRepositoryInterface::class);
        $chat->shouldReceive('create')->andReturnUsing(function (array $data): ChatMessage {
            if ($this->chatFails) {
                throw new \RuntimeException('test rollback');
            }

            return ChatMessage::create($data);
        });
        $this->app->instance(ChatMessageRepositoryInterface::class, $chat);
    }

    public function test_wrong_location_does_not_activate_or_reveal_target_or_reroll(): void
    {
        DB::table('users')->where('id', 1)->update(['location_id' => 7]);
        $this->user->refresh();
        $request = Request::create('/treasure/maps/1/check', 'POST');
        $request->setUserResolver(fn () => $this->user);
        $controller = app(TreasureHuntController::class);
        foreach (range(1, 3) as $_) {
            $response = $controller->check($request, 1);
            $this->assertSame(422, $response->status());
            $this->assertSame(['ok' => false, 'message' => __('treasure.wrong_location')], $response->getData(true));
        }
        $this->assertSame(1, TreasureHunt::count());
        $this->assertNull(TreasureHunt::first()->activated_at);
        $this->assertDatabaseCount('player_reputations', 0);
        $this->assertDatabaseCount('items', 1);
    }

    public function test_success_consumes_map_gives_closed_chest_points_and_private_message_once(): void
    {
        $attempt = $this->begin();
        $this->travel(12)->seconds();
        $result = app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']);
        $this->assertTrue($result['found']);
        $this->assertDatabaseMissing('items', ['id' => 1]);
        $this->assertDatabaseHas('items', ['id' => $result['reward']['id'], 'share_item_id' => 2, 'is_open' => 0]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => $result['reward']['id'], 'count' => 1]);
        $this->assertDatabaseHas('player_reputations', ['player_id' => 1, 'points' => 10]);
        $this->assertDatabaseHas('chat_messages', ['target_user_id' => 1, 'channel' => 'main', 'type' => 'loot', 'translation_key' => 'chat.treasure_found']);
        $this->assertNotNull(TreasureHunt::first()->found_at);
        $this->expectException(ValidationException::class);
        try {
            app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']);
        } finally {
            $this->assertDatabaseCount('items', 1);
            $this->assertDatabaseCount('chat_messages', 1);
        }
    }

    public function test_failures_keep_map_and_do_not_increase_chance_or_grant_points(): void
    {
        $this->success = false;
        $service = app(TreasureHuntService::class);
        $service->check($this->user, 1);
        foreach (range(1, 10) as $_) {
            $attempt = $service->start($this->user, 1);
            $this->assertSame(70.0, TreasureHunt::first()->chance_snapshot);
            $this->travel(12)->seconds();
            $this->assertFalse($service->complete($this->user, 1, $attempt['token'])['found']);
        }
        $this->assertDatabaseCount('items', 1);
        $this->assertDatabaseCount('player_reputations', 0);
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_cannot_complete_before_timer_or_with_stale_cancelled_token(): void
    {
        $attempt = $this->begin();
        $this->reject(fn () => app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']));
        app(TreasureHuntService::class)->cancel($this->user, 1, $attempt['token']);
        $new = app(TreasureHuntService::class)->start($this->user, 1);
        $this->assertNotSame($new['token'], $attempt['token']);
        $this->reject(fn () => app(TreasureHuntService::class)->cancel($this->user, 1, $attempt['token']));
        $this->travel(12)->seconds();
        $this->reject(fn () => app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']));
        $this->assertTrue(app(TreasureHuntService::class)->complete($this->user, 1, $new['token'])['found']);
    }

    public function test_reputation_threshold_and_owner_are_enforced_before_activation(): void
    {
        TreasureMapConfig::first()->update(['required_reputation' => 500]);
        $this->reject(fn () => app(TreasureHuntService::class)->check($this->user, 1));
        $this->assertDatabaseCount('treasure_hunts', 1);
        $this->assertNull(TreasureHunt::first()->activated_at);
        PlayerReputation::create(['player_id' => 1, 'reputation_id' => 1, 'points' => 500]);
        DB::table('backpacks')->update(['user_id' => 2]);
        $this->reject(fn () => app(TreasureHuntService::class)->check($this->user, 1));
        DB::table('backpacks')->update(['user_id' => 1]);
        $this->assertTrue(app(TreasureHuntService::class)->check($this->user, 1)['ok']);
    }

    public function test_bonus_changes_chance_and_duration_and_activation_reveals_location_in_description_only(): void
    {
        DB::table('treasure_reputation_bonuses')->insert(['reputation_tier_id' => 1, 'search_chance_bonus_percent' => 40, 'search_speed_bonus_percent' => 50]);
        $this->assertStringNotContainsString('№6', Item::find(1)->getName());
        $this->assertStringNotContainsString('№6', Item::find(1)->getDescription());
        $attempt = $this->begin();
        $this->assertSame(6000, $attempt['completes_at'] - $attempt['started_at']);
        $this->assertSame(100.0, TreasureHunt::first()->chance_snapshot);
        $this->assertSame('Карта', Item::find(1)->getName());
        $this->assertStringContainsString('<b>[локация №6]</b>', Item::find(1)->getDescription());
    }

    public function test_reward_failure_rolls_back_consumption_chest_reputation_and_attempt(): void
    {
        $attempt = $this->begin();
        $this->travel(12)->seconds();
        $this->chatFails = true;
        try {
            app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']);
            $this->fail('Expected rollback');
        } catch (\RuntimeException $error) {
            $this->assertSame('test rollback', $error->getMessage());
        }
        $this->assertDatabaseCount('items', 1);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseCount('player_reputations', 0);
        $this->assertSame($attempt['token'], TreasureHunt::first()->search_token);
        $this->chatFails = false;
        $this->assertTrue(app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token'])['found']);
    }

    public function test_movement_cancels_search_and_keeps_activated_map(): void
    {
        $attempt = $this->begin();
        $this->user->location_id = 7;
        $this->user->save();
        $this->assertNull(TreasureHunt::first()->search_token);
        $this->assertNotNull(TreasureHunt::first()->activated_at);
        $this->assertNull(app(TreasureHuntService::class)->activeAtLocation($this->user));
        $this->reject(fn () => app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']));
    }

    public function test_dead_battle_gathering_and_lockpicking_block_search(): void
    {
        app(TreasureHuntService::class)->check($this->user, 1);
        foreach (['gathering_attempts', 'lockpicking_attempts'] as $table) {
            DB::table($table)->insert(['player_id' => 1, 'expires_at' => now()->addMinute()]);
            $this->reject(fn () => app(TreasureHuntService::class)->start($this->user, 1));
            DB::table($table)->delete();
        }
        DB::table('players')->update(['hp_now' => 0]);
        $this->reject(fn () => app(TreasureHuntService::class)->start($this->user, 1));
        DB::table('players')->update(['hp_now' => 100]);
        DB::table('battles')->insert(['id' => 1, 'status' => 1, 'location_id' => 6]);
        DB::table('battle_details')->insert(['user_id' => 1, 'battle_id' => 1, 'status' => 1]);
        $this->reject(fn () => app(TreasureHuntService::class)->start($this->user, 1));
        $this->assertDatabaseCount('items', 1);
    }

    public function test_only_one_map_can_be_activated_and_expired_search_does_not_consume_it(): void
    {
        $attempt = $this->begin();
        DB::table('items')->insert(['id' => 3, 'share_item_id' => 1]);
        DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => 3]);
        app(AssignTreasureDestination::class)->execute(1, 3);
        $this->reject(fn () => app(TreasureHuntService::class)->check($this->user, 3));
        $this->assertTrue(TreasureHunt::isSearching(1));
        $this->travel(133)->seconds();
        $result = app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']);
        $this->assertFalse($result['ok']);
        $this->assertNull(TreasureHunt::first()->search_token);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 1]);
        $this->assertDatabaseCount('player_reputations', 0);
    }

    public function test_existing_reputation_service_receives_award_without_starting_quest_cooldown(): void
    {
        $this->app->instance(ReputationService::class, new ReputationService);
        $attempt = $this->begin();
        $this->travel(12)->seconds();
        $result = app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']);
        $this->assertTrue($result['found']);
        $this->assertDatabaseHas('player_reputations', ['player_id' => 1, 'reputation_id' => 1, 'points' => 10]);
    }

    public function test_disabled_map_and_configuration_cannot_start_or_reward(): void
    {
        $attempt = $this->begin();
        $this->travel(12)->seconds();
        TreasureMapConfig::first()->update(['is_active' => false]);
        $this->reject(fn () => app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']));
        $this->assertSame($attempt['token'], TreasureHunt::first()->search_token);
        $this->assertDatabaseCount('backpacks', 1);
        TreasureMapConfig::first()->update(['is_active' => true]);
        DB::table('share_items')->where('id', 1)->update(['is_active' => false]);
        $this->reject(fn () => app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']));
    }

    public function test_admin_saves_configuration_and_validates_item_types_and_locations(): void
    {
        $data = [
            'share_item_id' => 1, 'reward_share_item_id' => 2, 'reputation_id' => 1, 'map_id' => 1,
            'required_reputation' => 500, 'reputation_reward' => 25, 'search_duration_seconds' => 20,
            'success_chance_percent' => 45, 'is_active' => 1, 'locations' => [7],
            'clue_ru' => 'Новая подсказка', 'clue_uk' => 'Нова підказка',
        ];
        $validate = function (array $input) {
            $request = SaveTreasureMapRequest::create('/admin/treasure-maps/1', 'POST', $input);
            $route = (new Route('POST', 'admin/treasure-maps/{config}', fn () => null))->bind($request);
            $request->setRouteResolver(fn () => $route);
            $validator = Validator::make($input, $request->rules());
            foreach ($request->after() as $callback) {
                $validator->after($callback);
            }

            return $validator;
        };
        $validator = $validate($data);
        $this->assertFalse($validator->fails(), json_encode($validator->errors()->all()));
        $config = app(ManageTreasureMaps::class)->save($validator->validated(), 1);
        $this->assertSame(500, (int) $config->required_reputation);
        $this->assertSame(45.0, $config->success_chance_percent);
        $this->assertSame('Нова підказка', $config->getTranslation('clue', 'uk'));
        $this->assertNull($config->map_id);
        $this->assertArrayNotHasKey('map_id', $validator->validated());
        $this->assertTrue($validate([...$data, 'reward_share_item_id' => 1])->fails());
        DB::table('share_items')->where('id', 1)->update(['is_stackable' => true, 'is_give' => true, 'is_auction_sellable' => true, 'is_clan_warehouse_allowed' => true]);
        $this->assertFalse($validate($data)->fails());
    }

    public function test_destination_is_chosen_on_receipt_through_pivot_and_not_on_check(): void
    {
        $random = \Mockery::mock(TreasureSearchRandom::class);
        $random->shouldReceive('territory')->once()->with([1])->andReturn(1);
        $random->shouldReceive('location')->once()->with([6, 7])->andReturn(7);
        $this->app->instance(TreasureSearchRandom::class, $random);
        DB::table('items')->insert(['id' => 3, 'share_item_id' => 1]);
        $this->user->backpack()->attach(3, ['count' => 1]);
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => 3, 'user_id' => 1, 'target_location_id' => 7, 'activated_at' => null]);
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => 1, 'target_location_id' => 6]);
        foreach (range(1, 3) as $_) {
            $this->reject(fn () => app(TreasureHuntService::class)->check($this->user, 3));
        }
        DB::table('users')->where('id', 1)->update(['location_id' => 7]);
        $this->assertTrue(app(TreasureHuntService::class)->check($this->user->fresh(), 3)['ok']);
    }

    public function test_invalid_destination_rolls_back_inventory_entry(): void
    {
        DB::table('locations')->update(['dungeon_id' => 9]);
        DB::table('items')->insert(['id' => 3, 'share_item_id' => 1]);
        $this->reject(fn () => $this->user->backpack()->attach(3));
        $this->assertDatabaseMissing('backpacks', ['item_id' => 3]);
        $this->reject(fn () => Backpack::create(['user_id' => 1, 'item_id' => 3]));
        $this->assertDatabaseMissing('backpacks', ['item_id' => 3]);
        $this->assertDatabaseCount('treasure_hunts', 1);
    }

    public function test_four_distinct_fragments_create_one_map_and_repeated_request_does_not_duplicate_it(): void
    {
        $this->createFragments();
        foreach ([11, 12, 13, 14] as $id) {
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $id]);
            Backpack::create(['user_id' => 1, 'item_id' => $id, 'count' => $id === 11 ? 4 : 1]);
        }
        $result = app(TreasureHuntService::class)->assemble($this->user, 11);
        $this->assertTrue($result['ok']);
        $this->assertDatabaseHas('backpacks', ['item_id' => 11, 'count' => 3]);
        $this->assertDatabaseMissing('backpacks', ['item_id' => 12]);
        $this->assertSame(2, DB::table('items')->where('share_item_id', 1)->count());
        $this->assertDatabaseCount('treasure_hunts', 2);
        $this->assertFalse(app(TreasureHuntService::class)->assemble($this->user, 11)['ok']);
        $this->assertSame(2, DB::table('items')->where('share_item_id', 1)->count());
    }

    public function test_identical_or_mixed_tier_fragments_cannot_form_a_map(): void
    {
        $this->createFragments();
        DB::table('items')->insert(['id' => 11, 'share_item_id' => 11]);
        Backpack::create(['user_id' => 1, 'item_id' => 11, 'count' => 4]);
        $this->assertFalse(app(TreasureHuntService::class)->assemble($this->user, 11)['ok']);
        $this->assertDatabaseHas('backpacks', ['item_id' => 11, 'count' => 4]);
        DB::table('share_items')->where('id', 12)->update(['rarity' => 'rare']);
        $this->reject(fn () => app(TreasureHuntService::class)->assemble($this->user, 11));
        $this->assertDatabaseCount('treasure_hunts', 1);
    }

    public function test_failed_map_delivery_rolls_back_all_four_fragments(): void
    {
        $this->createFragments();
        foreach ([11, 12, 13, 14] as $id) {
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $id]);
            Backpack::create(['user_id' => 1, 'item_id' => $id]);
        }
        DB::table('locations')->update(['dungeon_id' => 9]);
        $this->reject(fn () => app(TreasureHuntService::class)->assemble($this->user, 11));
        foreach ([11, 12, 13, 14] as $id) {
            $this->assertDatabaseHas('backpacks', ['item_id' => $id, 'count' => 1]);
        }
        $this->assertDatabaseCount('items', 5);
        $this->assertDatabaseCount('treasure_hunts', 1);
    }

    public function test_admin_requires_four_distinct_same_tier_parts_and_one_fixed_map(): void
    {
        $this->createFragments();
        $data = ['name' => 'Карта', 'type' => 'treasure_map', 'result_mode' => 'fixed', 'is_active' => true,
            'components' => array_map(fn ($id) => ['share_item_id' => $id, 'count' => 1], [11, 12, 13, 14]),
            'rewards' => [['share_item_id' => 1, 'count' => 1, 'weight' => 1]]];
        $validate = function (array $data) {
            $request = SaveItemAssemblyRequest::create('/', 'POST', $data);
            $validator = Validator::make($data, $request->rules());
            foreach ($request->after() as $callback) {
                $validator->after($callback);
            }

            return $validator;
        };
        $this->assertFalse($validate($data)->fails());
        $this->assertTrue($validate([...$data, 'components' => [['share_item_id' => 11, 'count' => 4]]])->fails());
        $this->assertTrue($validate([...$data, 'result_mode' => 'random'])->fails());
        DB::table('share_items')->where('id', 12)->update(['rarity' => 'rare']);
        $this->assertTrue($validate($data)->fails());
    }

    public function test_assembly_randomizes_territory_then_location_for_each_instance_and_shows_only_territory(): void
    {
        $this->createFragments();
        DB::table('maps')->where('id', 1)->update(['name' => '{"ru":"Город","uk":"Місто"}']);
        DB::table('maps')->insert([
            ['id' => 2, 'name' => '{"ru":"Лес","uk":"Ліс"}'],
            ['id' => 3, 'name' => '{"ru":"Пустая карта"}'],
            ['id' => 4, 'name' => '{"ru":"Инстанс"}'],
        ]);
        DB::table('locations')->insert([
            ['id' => 8, 'map_id' => 2, 'dungeon_id' => null],
            ['id' => 9, 'map_id' => 2, 'dungeon_id' => null],
            ['id' => 10, 'map_id' => 2, 'dungeon_id' => 99],
            ['id' => 11, 'map_id' => 4, 'dungeon_id' => 99],
        ]);
        foreach ([11, 12, 13, 14] as $id) {
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $id]);
            Backpack::create(['user_id' => 1, 'item_id' => $id, 'count' => 2]);
        }
        $random = \Mockery::mock(TreasureSearchRandom::class);
        $random->shouldReceive('territory')->twice()->with([1, 2])->andReturn(2, 1);
        $random->shouldReceive('location')->once()->with([8, 9])->andReturn(9);
        $random->shouldReceive('location')->once()->with([6, 7])->andReturn(7);
        $this->app->instance(TreasureSearchRandom::class, $random);
        $messages = [];
        foreach (['ru', 'uk'] as $locale) {
            app()->setLocale($locale);
            $result = app(TreasureHuntService::class)->assemble($this->user, 11);
            $this->assertTrue($result['ok']);
            $messages[] = $result['message'];
        }
        $this->assertSame('Получено: Карта [Лес].', $messages[0]);
        $this->assertSame('Отримано: Карта [Місто].', $messages[1]);
        $generated = TreasureHunt::where('map_item_id', '!=', 1)->orderBy('id')->get();
        $this->assertSame([2, 1], $generated->pluck('target_map_id')->all());
        $this->assertSame([9, 7], $generated->pluck('target_location_id')->all());
        $item = Item::findOrFail($generated[0]->map_item_id);
        app()->setLocale('ru');
        $this->assertSame('Карта', $item->getName());
        $this->assertStringContainsString('«<b>Лес</b>»', $item->getDescription());
        app()->setLocale('uk');
        $this->assertSame('Карта', $item->getName());
        $this->assertStringContainsString('«<b>Ліс</b>»', $item->getDescription());
        $this->assertStringNotContainsString('№9', $item->getDescription());
        $this->reject(fn () => app(TreasureHuntService::class)->check($this->user, $item->id));
        DB::table('users')->where('id', 1)->update(['location_id' => 9]);
        $this->assertTrue(app(TreasureHuntService::class)->check($this->user->fresh(), $item->id)['ok']);
        $this->assertSame('Карта', $item->fresh()->getName());
        $this->assertStringContainsString('<b>[локація №9]</b>', $item->fresh()->getDescription());
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => $item->id, 'target_map_id' => 2, 'target_location_id' => 9]);
    }

    public function test_migration_preserves_existing_destination(): void
    {
        $migration = require database_path('migrations/2026_10_31_000002_randomize_treasure_map_territories.php');
        $migration->down();
        $migration->up();
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => 1, 'user_id' => 1, 'target_location_id' => 6, 'target_map_id' => 1]);
    }

    public function test_invalid_assembly_reports_configuration_id_and_logs_specific_problem_without_consuming_parts(): void
    {
        $this->createFragments();
        DB::table('items')->insert(['id' => 11, 'share_item_id' => 11]);
        Backpack::create(['user_id' => 1, 'item_id' => 11]);
        DB::table('share_items')->where('id', 12)->update(['rarity' => 'rare']);
        Log::spy();
        $request = Request::create('/treasure/fragments/11/assemble', 'POST');
        $request->setUserResolver(fn () => $this->user);
        $response = app(TreasureHuntController::class)->assemble($request, 11);
        $this->assertSame(422, $response->status());
        $this->assertSame(__('treasure.assembly_configuration_error', ['id' => 1]), $response->getData(true)['message']);
        Log::shouldHaveReceived('error')->once()->with('Invalid treasure map assembly configuration.', \Mockery::on(fn ($context) => $context['assembly_id'] === 1 && in_array('rarity_mismatch', $context['violations'], true)));
        $this->assertDatabaseHas('backpacks', ['item_id' => 11, 'count' => 1]);
        $this->assertDatabaseCount('treasure_hunts', 1);
    }

    public function test_wrong_result_mode_is_reported_as_invalid_configuration(): void
    {
        $this->createFragments();
        DB::table('items')->insert(['id' => 11, 'share_item_id' => 11]);
        Backpack::create(['user_id' => 1, 'item_id' => 11]);
        ItemAssembly::first()->update(['result_mode' => 'random']);
        Log::spy();
        $this->reject(fn () => app(TreasureHuntService::class)->assemble($this->user, 11));
        Log::shouldHaveReceived('error')->once()->with('Invalid treasure map assembly configuration.', \Mockery::on(fn ($context) => in_array('result_must_be_fixed', $context['violations'], true)));
    }

    public function test_known_ordinary_item_is_inserted_without_any_destination_lookup(): void
    {
        $item = Item::create(['share_item_id' => 2]);
        $item->load('itemInfo');
        DB::enableQueryLog();
        DB::flushQueryLog();
        Backpack::createForItem(userId: 1, item: $item);
        $selects = array_filter(DB::getQueryLog(), fn ($query) => str_starts_with(strtolower(ltrim($query['query'])), 'select'));
        DB::disableQueryLog();
        $this->assertSame([], array_values($selects));
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => $item->id]);
        $this->assertDatabaseCount('treasure_hunts', 1);
    }

    public function test_known_treasure_map_still_receives_destination(): void
    {
        $item = Item::create(['share_item_id' => 1]);
        $item->load('itemInfo');
        Backpack::createForItem(userId: 1, item: $item);
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => $item->id, 'user_id' => 1, 'target_map_id' => 1, 'target_location_id' => 6]);
    }

    public function test_admin_must_save_map_as_draft_before_configuration_exists(): void
    {
        $fill = new \ReflectionMethod(ItemController::class, 'fillItem');
        $data = ['name' => 'Новая карта', 'type' => 'treasure_map', 'is_active' => '1'];
        $draft = new ShareItem;
        try {
            $fill->invoke(new ItemController, $draft, Request::create('/', 'POST', $data));
            $this->fail('An unconfigured map was published.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('is_active', $error->errors());
        }
        $fill->invoke(new ItemController, $draft, Request::create('/', 'POST', [...$data, 'is_active' => '0']));
        $this->assertFalse((bool) $draft->is_active);
        $this->assertSame('treasure_map', $draft->type->value);
    }

    public function test_admin_can_activate_only_a_map_with_existing_configuration(): void
    {
        DB::table('share_items')->insert(['id' => 3, 'name' => '{"ru":"Карта без настроек"}', 'type' => 'treasure_map', 'is_active' => false]);
        $fill = new \ReflectionMethod(ItemController::class, 'fillItem');
        $request = Request::create('/', 'POST', ['name' => 'Карта', 'type' => 'treasure_map', 'is_active' => '1']);
        $this->reject(fn () => $fill->invoke(new ItemController, ShareItem::findOrFail(3), $request));
        $configured = ShareItem::findOrFail(1);
        $fill->invoke(new ItemController, $configured, $request);
        $this->assertTrue((bool) $configured->is_active);
    }

    public function test_reassigning_configuration_cannot_orphan_a_published_map(): void
    {
        DB::table('share_items')->insert(['id' => 3, 'name' => '{"ru":"Другая карта"}', 'type' => 'treasure_map', 'is_active' => false]);
        $config = TreasureMapConfig::first();
        $data = [
            'share_item_id' => 3, 'reward_share_item_id' => 2, 'reputation_id' => 1,
            'required_reputation' => 0, 'reputation_reward' => 10, 'search_duration_seconds' => 12,
            'success_chance_percent' => 70, 'is_active' => 1, 'clue_ru' => 'Подсказка', 'clue_uk' => 'Підказка',
        ];
        $this->reject(fn () => app(ManageTreasureMaps::class)->save($data, $config->id));
        $this->assertSame(1, (int) $config->fresh()->share_item_id);
        DB::table('share_items')->where('id', 1)->update(['is_active' => false]);
        app(ManageTreasureMaps::class)->save($data, $config->id);
        $this->assertSame(3, (int) $config->fresh()->share_item_id);
    }

    public function test_inventory_fallback_logs_missing_configuration_and_rolls_back_entry(): void
    {
        DB::table('share_items')->insert(['id' => 3, 'name' => '{"ru":"Карта без настроек"}', 'type' => 'treasure_map']);
        $item = Item::create(['share_item_id' => 3]);
        $item->load('itemInfo');
        Log::spy();
        try {
            Backpack::createForItem(userId: 1, item: $item);
            $this->fail('An unconfigured map entered the backpack.');
        } catch (ValidationException $error) {
            $this->assertSame(__('treasure.map_configuration_missing', ['id' => 3]), $error->errors()['treasure'][0]);
        }
        Log::shouldHaveReceived('error')->once()->with('Treasure map item has no search configuration.', [
            'share_item_id' => 3, 'item_id' => (int) $item->id, 'user_id' => 1,
        ]);
        $this->assertDatabaseMissing('backpacks', ['item_id' => $item->id]);
        $this->assertDatabaseCount('treasure_hunts', 1);
    }

    public function test_item_navigation_links_to_its_search_configuration_or_prefilled_creation(): void
    {
        $html = view('admin.item.navigation', ['item' => ShareItem::findOrFail(1)])->render();
        $this->assertStringContainsString('Конфиг поиска', $html);
        $this->assertStringContainsString(route('admin.treasure_maps.edit', 1), $html);
        DB::table('share_items')->insert(['id' => 3, 'name' => '{"ru":"Новая карта"}', 'type' => 'treasure_map', 'is_active' => false]);
        $html = view('admin.item.navigation', ['item' => ShareItem::findOrFail(3)])->render();
        $this->assertStringContainsString('Создать конфиг поиска', $html);
        $this->assertStringContainsString(route('admin.treasure_maps.create', ['share_item_id' => 3]), $html);
        $html = view('admin.item.navigation', ['item' => ShareItem::findOrFail(2)])->render();
        $this->assertStringNotContainsString('конфиг поиска', mb_strtolower($html));
    }

    public function test_search_configuration_form_preselects_item_from_item_card(): void
    {
        DB::table('share_items')->insert(['id' => 3, 'name' => '{"ru":"Новая карта"}', 'type' => 'treasure_map', 'is_active' => false]);
        $controller = app(TreasureMapController::class);
        $view = $controller->form(Request::create('/admin/treasure-maps/create?share_item_id=3'));
        $data = $view->getData();
        $this->assertSame(3, (int) $data['config']->share_item_id);
        $this->assertTrue($data['selectedItems']->has(3));
        $this->reject(fn () => $controller->form(Request::create('/admin/treasure-maps/create?share_item_id=2')));
        $response = $controller->form(Request::create('/admin/treasure-maps/create?share_item_id=1'));
        $this->assertSame(route('admin.treasure_maps.edit', 1), $response->getTargetUrl());
    }

    public function test_hotbar_map_check_activates_without_consuming_and_clears_slot_only_after_finding_treasure(): void
    {
        $this->actingAs($this->user);
        $player = $this->user->player->setRelation('playerEquip', null);
        $this->assertNull(app(HotbarService::class)->setSlot($player, 1, 'item', 1));
        $request = Request::create('/hotbar/use', 'POST', ['slot' => 1]);
        foreach (range(1, 2) as $_) {
            $response = app(HotbarController::class)->use($request);
            $this->assertSame(200, $response->status());
            $data = $response->getData(true);
            $this->assertSame('success', $data['status']);
            $this->assertSame('treasure_check', $data['action']);
            $this->assertFalse($data['slot_cleared']);
            $this->assertSame(1, $data['count']);
            $this->assertArrayNotHasKey('hp_now', $data);
            $this->assertDatabaseHas('backpacks', ['item_id' => 1, 'count' => 1]);
            $this->assertDatabaseCount('player_slots', 1);
        }
        $this->assertNotNull(TreasureHunt::first()->activated_at);
        $attempt = app(TreasureHuntService::class)->start($this->user, 1);
        $this->travel(12)->seconds();
        $result = app(TreasureHuntService::class)->complete($this->user, 1, $attempt['token']);
        $this->assertTrue($result['found']);
        $this->assertDatabaseCount('player_slots', 0);
    }

    public function test_failed_hotbar_map_check_returns_same_message_as_backpack_and_keeps_map(): void
    {
        DB::table('users')->where('id', 1)->update(['location_id' => 7]);
        $this->actingAs($this->user->fresh());
        PlayerSlot::create(['player_id' => 1, 'slot_number' => 1, 'entity_type' => 'item', 'entity_id' => 1]);
        $request = Request::create('/hotbar/use', 'POST', ['slot' => 1]);
        $response = app(HotbarController::class)->use($request);
        $this->assertSame(422, $response->status());
        $this->assertSame('treasure_check', $response->getData(true)['action']);
        $this->assertSame(__('treasure.wrong_location'), $response->getData(true)['message']);
        $this->assertDatabaseHas('backpacks', ['item_id' => 1, 'count' => 1]);
        $this->assertDatabaseCount('player_slots', 1);
        $this->assertNull(TreasureHunt::first()->activated_at);
        TreasureMapConfig::first()->update(['required_reputation' => 500]);
        $response = app(HotbarController::class)->use($request);
        $this->assertSame(422, $response->status());
        $this->assertSame(__('treasure.reputation_required', ['required' => 500, 'current' => 0]), $response->getData(true)['message']);
        $this->assertContains('prison.context', app('router')->getRoutes()->getByName('hotbar.use')->gatherMiddleware());
    }

    public function test_hotbar_cannot_use_a_map_removed_from_inventory_or_disabled_for_slots(): void
    {
        $this->actingAs($this->user);
        PlayerSlot::create(['player_id' => 1, 'slot_number' => 1, 'entity_type' => 'item', 'entity_id' => 1]);
        DB::table('share_items')->where('id', 1)->update(['is_slot_usable' => false]);
        $request = Request::create('/hotbar/use', 'POST', ['slot' => 1]);
        $this->assertSame(422, app(HotbarController::class)->use($request)->status());
        $this->assertNull(TreasureHunt::first()->activated_at);
        DB::table('backpacks')->where('item_id', 1)->update(['user_id' => 2]);
        $response = app(HotbarController::class)->use($request);
        $this->assertSame(422, $response->status());
        $this->assertTrue($response->getData(true)['slot_cleared']);
        $this->assertDatabaseCount('player_slots', 0);
        $this->assertDatabaseHas('backpacks', ['item_id' => 1, 'user_id' => 2, 'count' => 1]);
    }

    private function createFragments(): void
    {
        (require database_path('migrations/2026_10_19_000002_create_item_assemblies.php'))->up();
        $assembly = ItemAssembly::create([
            'name' => ['ru' => 'Карта'], 'type' => 'treasure_map', 'result_mode' => 'fixed', 'is_active' => true,
        ]);
        foreach ([11, 12, 13, 14] as $id) {
            DB::table('share_items')->insert(['id' => $id, 'name' => '{"ru":"Фрагмент"}', 'type' => 'treasure_map_fragment']);
            $assembly->components()->attach($id, ['count' => 1]);
        }
        $assembly->rewards()->attach(1, ['count' => 1, 'weight' => 1]);
    }

    private function begin(): array
    {
        $service = app(TreasureHuntService::class);
        $this->assertTrue($service->check($this->user, 1)['ok']);

        return $service->start($this->user, 1);
    }

    private function reject(\Closure $action): void
    {
        try {
            $action();
            $this->fail('Expected validation error');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }
    }
}
