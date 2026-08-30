<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Interface;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Interface\Application\Listeners\UpdatePlayerPresenceFromSocket;
use App\Modules\Interface\Domain\Events\PlayerStateUpdated;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Reverb\Application;
use Laravel\Reverb\Connection;
use Laravel\Reverb\Contracts\WebSocketConnection;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Protocols\Pusher\Channels\Channel;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PlayerHeartbeatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('game.player_heartbeat_seconds', 10);
        DB::purge('sqlite');

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_authenticated_heartbeat_updates_online_and_applies_poison_tick(): void
    {
        $now = Carbon::parse('2026-08-21 14:00:00');
        Carbon::setTestNow($now);

        $race = (new Race)->forceFill([
            'name' => 'Human',
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 1,
            'free_stats' => 0,
        ]);
        $race->save();
        $user = (new User)->forceFill([
            'name' => 'Heartbeat Tester',
            'email' => 'heartbeat@example.test',
            'password' => Hash::make('secret'),
        ]);
        $user->save();
        $player = (new Player)->forceFill([
            'user_id' => $user->id,
            'race_id' => $race->id,
            'lvl' => 8,
            'exp' => 0,
            'exp_up' => 100,
            'exp_diff' => 100,
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 3,
            'hp_now' => 100,
            'hp_max' => 100,
            'mp_now' => 10,
            'mp_max' => 10,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'free_stats' => 0,
            'last_regen_at' => $now,
        ]);
        $player->save();
        $user->forceFill(['player_id' => $player->id])->save();
        $battle = Battle::query()->create(['status' => 1]);
        PlayerActiveEffect::query()->create([
            'player_id' => $player->id,
            'battle_id' => $battle->id,
            'type' => ActiveEffectType::POISON,
            'applied_at' => $now->copy()->subSeconds(10),
            'last_tick_at' => $now->copy()->subSeconds(10),
            'stacks' => 2,
            'current_value' => 7,
        ]);

        $response = $this->actingAs($user->fresh())->postJson(route('player.heartbeat'));

        $response->assertOk()
            ->assertJsonPath('hp.current', 93)
            ->assertJsonPath('hp.max', 100)
            ->assertJsonPath('effect_damage', 7)
            ->assertJsonPath('effects.0.name', 'Отравление')
            ->assertJsonPath('effects.0.duration', 10)
            ->assertJsonPath('effects.0.is_curse', true);

        $this->assertSame(93, $player->fresh()->hp_now);
        $this->assertTrue($user->fresh()->last_online_at->equalTo($now));
    }

    public function test_heartbeat_requires_authentication(): void
    {
        $this->postJson(route('player.heartbeat'))->assertUnauthorized();
    }

    public function test_server_tick_processes_due_poison_and_broadcasts_private_player_state(): void
    {
        $now = Carbon::parse('2026-08-21 14:00:00');
        Carbon::setTestNow($now);

        $race = (new Race)->forceFill([
            'name' => 'Human',
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 1,
            'free_stats' => 0,
        ]);
        $race->save();
        $user = (new User)->forceFill([
            'name' => 'Socket Tester',
            'email' => 'socket@example.test',
            'password' => Hash::make('secret'),
        ]);
        $user->save();
        $player = (new Player)->forceFill([
            'user_id' => $user->id,
            'race_id' => $race->id,
            'lvl' => 8,
            'exp' => 0,
            'exp_up' => 100,
            'exp_diff' => 100,
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 3,
            'hp_now' => 100,
            'hp_max' => 100,
            'mp_now' => 10,
            'mp_max' => 10,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'free_stats' => 0,
            'last_regen_at' => $now,
        ]);
        $player->save();
        $user->forceFill(['player_id' => $player->id])->save();
        $battle = Battle::query()->create(['status' => 1]);
        PlayerActiveEffect::query()->create([
            'player_id' => $player->id,
            'battle_id' => $battle->id,
            'type' => ActiveEffectType::POISON,
            'applied_at' => $now->copy()->subSeconds(10),
            'last_tick_at' => $now->copy()->subSeconds(10),
            'stacks' => 2,
            'current_value' => 7,
        ]);

        Event::fake([PlayerStateUpdated::class]);

        $this->artisan('players:process-state')->assertSuccessful();

        $this->assertSame(93, $player->fresh()->hp_now);
        $this->assertNull($user->fresh()->last_online_at);
        Event::assertDispatched(
            PlayerStateUpdated::class,
            fn (PlayerStateUpdated $event): bool => $event->playerId === $player->id
                && $event->state['hp']['current'] === 93
                && $event->state['effect_damage'] === 7,
        );
    }

    public function test_server_tick_regenerates_active_player_against_resolved_hp_max(): void
    {
        $now = Carbon::parse('2026-08-21 14:15:00');
        Carbon::setTestNow($now);

        $race = (new Race)->forceFill([
            'name' => 'Human',
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 1,
            'free_stats' => 0,
        ]);
        $race->save();
        $user = (new User)->forceFill([
            'name' => 'Regeneration Tester',
            'email' => 'regeneration@example.test',
            'password' => Hash::make('secret'),
            'last_online_at' => $now,
        ]);
        $user->save();
        $player = (new Player)->forceFill([
            'user_id' => $user->id,
            'race_id' => $race->id,
            'lvl' => 8,
            'exp' => 0,
            'exp_up' => 100,
            'exp_diff' => 100,
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 60,
            'hp_now' => 150,
            'hp_max' => 100,
            'mp_now' => 10,
            'mp_max' => 10,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'free_stats' => 0,
            'last_regen_at' => $now->copy()->subSeconds(Player::REGEN_INTERVAL),
        ]);
        $player->save();
        $user->forceFill(['player_id' => $player->id])->save();

        Event::fake([PlayerStateUpdated::class]);

        $this->artisan('players:process-state')->assertSuccessful();

        $this->assertSame(151, $player->fresh()->hp_now);
        Event::assertDispatched(
            PlayerStateUpdated::class,
            fn (PlayerStateUpdated $event): bool => $event->playerId === $player->id
                && $event->state['hp'] === ['current' => 151, 'max' => 271],
        );
    }

    public function test_game_page_subscribes_to_player_websocket_without_periodic_heartbeat(): void
    {
        $user = (new User)->forceFill([
            'name' => 'Socket View Tester',
            'email' => 'socket-view@example.test',
            'password' => Hash::make('secret'),
            'player_id' => 77,
        ]);
        $user->save();
        $this->withoutVite();

        $response = $this->actingAs($user)->get(route('game'));

        $response->assertOk()
            ->assertSee('window.Echo.private(playerStateChannelName)', false)
            ->assertSee(".listen('.player.state.updated'", false)
            ->assertSee(".listen('.post.unread.updated'", false)
            ->assertSee("whisper('player-presence'", false)
            ->assertDontSee('setInterval(syncPlayerHeartbeat', false)
            ->assertDontSee('post/unread-count', false);
    }

    public function test_menu_keeps_blink_state_until_canvas_menu_is_ready(): void
    {
        $response = $this->get(route('menu'));

        $response->assertOk()
            ->assertSee('var menuButtonBlinkStates = {};', false)
            ->assertSee('menuButtonBlinkStates[name] = !!status;', false)
            ->assertSee('Object.keys(menuButtonBlinkStates).forEach', false);
    }

    public function test_authenticated_socket_presence_updates_online_timestamp(): void
    {
        $now = Carbon::parse('2026-08-21 14:30:00');
        Carbon::setTestNow($now);
        $user = (new User)->forceFill([
            'name' => 'Socket Presence Tester',
            'email' => 'socket-presence@example.test',
            'password' => Hash::make('secret'),
            'player_id' => 91,
        ]);
        $user->save();

        $application = new Application(
            id: 'test-app',
            key: 'test-key',
            secret: 'test-secret',
            pingInterval: 60,
            activityTimeout: 30,
            allowedOrigins: ['*'],
            maxMessageSize: 10_000,
        );
        $socket = new class implements WebSocketConnection
        {
            public function id(): int|string
            {
                return 1;
            }

            public function send(mixed $message): void {}

            public function close(mixed $message = null): void {}
        };
        $connection = new Connection($socket, $application, null);
        $channel = Mockery::mock(Channel::class);
        $channel->shouldReceive('subscribed')->once()->with($connection)->andReturnTrue();
        $channels = Mockery::mock(ChannelManager::class);
        $channels->shouldReceive('for')->once()->with($application)->andReturnSelf();
        $channels->shouldReceive('find')->once()->with('private-player.91')->andReturn($channel);
        $event = new MessageReceived($connection, json_encode([
            'event' => 'client-player-presence',
            'channel' => 'private-player.91',
            'data' => ['sent_at' => 1],
        ], JSON_THROW_ON_ERROR));

        (new UpdatePlayerPresenceFromSocket($channels))->handle($event);

        $this->assertTrue($user->fresh()->last_online_at->equalTo($now));

        Carbon::setTestNow($now->copy()->addMinute());
        $unauthorizedChannel = Mockery::mock(Channel::class);
        $unauthorizedChannel->shouldReceive('subscribed')->once()->with($connection)->andReturnFalse();
        $unauthorizedChannels = Mockery::mock(ChannelManager::class);
        $unauthorizedChannels->shouldReceive('for')->once()->with($application)->andReturnSelf();
        $unauthorizedChannels->shouldReceive('find')->once()->with('private-player.91')->andReturn($unauthorizedChannel);

        (new UpdatePlayerPresenceFromSocket($unauthorizedChannels))->handle($event);

        $this->assertTrue($user->fresh()->last_online_at->equalTo($now));
    }

    public function test_player_can_authorize_only_own_private_state_channel(): void
    {
        $user = (new User)->forceFill([
            'name' => 'Channel Tester',
            'email' => 'channel@example.test',
            'password' => Hash::make('secret'),
            'player_id' => 77,
        ]);
        $user->save();
        config()->set('broadcasting.default', 'reverb');
        config()->set('broadcasting.connections.reverb.key', 'test-key');
        config()->set('broadcasting.connections.reverb.secret', 'test-secret');
        config()->set('broadcasting.connections.reverb.app_id', 'test-app');
        require base_path('routes/channels.php');

        $this->actingAs($user)
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-player.77',
            ])
            ->assertOk();

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-player.78',
        ])->assertForbidden();
    }

    public function test_poison_can_kill_afk_player_and_finalize_battle_death_once(): void
    {
        $now = Carbon::parse('2026-08-21 15:00:00');
        Carbon::setTestNow($now);

        $mapId = DB::table('maps')->insertGetId([
            'name' => 'Test map',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $respawnLocationId = DB::table('locations')->insertGetId([
            'map_id' => $mapId,
            'name' => 'Respawn',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $battleLocationId = DB::table('locations')->insertGetId([
            'map_id' => $mapId,
            'name' => 'Battlefield',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('maps')->where('id', $mapId)->update(['resp_location_id' => $respawnLocationId]);

        $race = (new Race)->forceFill([
            'name' => 'Human',
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 1,
            'free_stats' => 0,
        ]);
        $race->save();
        $user = (new User)->forceFill([
            'name' => 'Poisoned Tester',
            'email' => 'poisoned@example.test',
            'password' => Hash::make('secret'),
            'location_id' => $battleLocationId,
            'prev_location_id' => $battleLocationId,
        ]);
        $user->save();
        $player = (new Player)->forceFill([
            'user_id' => $user->id,
            'race_id' => $race->id,
            'lvl' => 8,
            'exp' => 80,
            'exp_up' => 100,
            'exp_diff' => 100,
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 3,
            'hp_now' => 5,
            'hp_max' => 100,
            'mp_now' => 10,
            'mp_max' => 10,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'free_stats' => 0,
            'victory' => 0,
            'death' => 0,
            'last_regen_at' => $now,
        ]);
        $player->save();
        $user->forceFill(['player_id' => $player->id])->save();

        $battle = Battle::query()->create([
            'location_id' => $battleLocationId,
            'rounds' => 0,
            'status' => 1,
        ]);
        $participant = BattleDetail::query()->create([
            'battle_id' => $battle->id,
            'user_id' => $user->id,
        ]);
        PlayerActiveEffect::query()->create([
            'player_id' => $player->id,
            'battle_id' => $battle->id,
            'type' => ActiveEffectType::POISON,
            'applied_at' => $now->copy()->subSeconds(10),
            'last_tick_at' => $now->copy()->subSeconds(10),
            'stacks' => 1,
            'current_value' => 10,
        ]);

        $this->mock(DungeonCoordinator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handlePlayerDeath')->once()->andReturnNull();
        });

        $response = $this->actingAs($user->fresh())->postJson(route('player.heartbeat'));

        $response->assertOk()
            ->assertJsonPath('dead', true)
            ->assertJsonPath('death_message', 'Вы погибли от периодического эффекта: Отравление. Опыт -10%.')
            ->assertJsonPath('death_url', route('location'))
            ->assertJsonPath('effect_damage', 10)
            ->assertJsonPath('hp.current', 100)
            ->assertJsonCount(0, 'effects');

        $this->assertSame(0, $participant->fresh()->status->value);
        $this->assertSame(1, $player->fresh()->death);
        $this->assertSame(70, $player->fresh()->exp);
        $this->assertSame(100, $player->fresh()->hp_now);
        $this->assertSame($respawnLocationId, $user->fresh()->location_id);
        $this->assertSame(1, $battle->fresh()->rounds);
        $this->assertDatabaseCount('player_active_effects', 0);
        $this->assertDatabaseCount('battle_rounds', 1);
        $this->assertStringContainsString('Отравление', BattleRound::query()->firstOrFail()->action);

        $secondResponse = $this->postJson(route('player.heartbeat'));

        $secondResponse->assertOk()->assertJsonPath('dead', false);
        $this->assertArrayNotHasKey('death_url', $secondResponse->json());
        $this->assertSame(1, $player->fresh()->death);
        $this->assertSame(70, $player->fresh()->exp);
        $this->assertDatabaseCount('battle_rounds', 1);
    }

    private function createSchema(): void
    {
        Schema::connection('sqlite')->create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('resp_location_id')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->integer('warehouse_count')->default(50);
            $table->integer('bag_count')->default(25);
            $table->integer('slot_count')->default(3);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('prev_location_id')->nullable();
            $table->timestamp('last_online_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('post_letters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('recipient_user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('recipient_deleted_at')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('races', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->float('strength');
            $table->float('agility');
            $table->float('intuition');
            $table->float('wisdom');
            $table->float('intelligence');
            $table->float('endurance');
            $table->integer('free_stats');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('players', function (Blueprint $table): void {
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
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->integer('mp_now');
            $table->integer('mp_max');
            $table->float('min_dmg');
            $table->float('max_dmg');
            $table->integer('free_stats');
            $table->integer('victory')->default(0);
            $table->integer('death')->default(0);
            $table->float('experience_multiplier')->default(1.0);
            $table->timestamp('last_regen_at')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('player_equipments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_passive')->default(false);
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('player_magic_skills', function (Blueprint $table): void {
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });
        Schema::connection('sqlite')->create('player_item_buffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('effect_type');
            $table->float('value');
            $table->string('value_type');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->json('stat_modifiers')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('rounds')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('battle_rounds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->integer('round_number')->default(1);
            $table->text('action');
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('next_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->float('tick_remainder')->default(0);
            $table->timestamps();
        });
    }
}
