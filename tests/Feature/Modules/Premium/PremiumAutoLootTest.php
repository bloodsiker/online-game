<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Premium;

use App\Modules\Auth\Application\Listeners\ActivateLoginSession;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\DropService;
use App\Modules\Battle\Application\Services\MonsterLootService;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Premium\Application\Services\PremiumBonusService;
use App\Modules\Premium\Domain\Enums\PremiumBonus;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumLevel;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumPeriod;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\User\Infrastructure\Persistence\Models\UserGameSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PremiumAutoLootTest extends TestCase
{
    private array $messages = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('lvl')->default(10);
            $table->integer('money')->default(0);
            $table->string('active_login_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->integer('lvl')->default(10);
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
        });
        Schema::create('battle_monster_damage', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('location_monster_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('damage');
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('location_monster_id');
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->boolean('is_stackable')->default(true);
            $table->boolean('drop_direct_to_backpack')->default(false);
            $table->integer('count_use')->default(0);
            $table->integer('max_drop_level_difference')->nullable();
            $table->integer('required_active_effect_id')->nullable();
        });
        Schema::create('monster_has_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('share_item_id');
            $table->float('drop_chance')->default(100);
            $table->integer('min_count')->default(1);
            $table->integer('max_count')->default(1);
        });
        Schema::create('share_item_instant_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('reward_type');
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
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
        Schema::create('item_on_locations', function (Blueprint $table): void {
            $table->unsignedBigInteger('loot_owner_id')->nullable();
            $table->timestamp('loot_reserved_until')->nullable();
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('count');
            $table->string('interaction_type');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->timestamp('expires_at')->nullable();
        });
        (require database_path('migrations/2026_10_21_000002_create_premium_system.php'))->up();
        foreach (PremiumLevel::all() as $level) {
            $level->update(['required_diamond_cents' => $level->level * 1000]);
        }
        (require database_path('migrations/2026_10_30_000001_add_premium_auto_loot_bonus.php'))->up();
        (require database_path('migrations/2026_10_10_010000_create_user_game_settings_table.php'))->up();
        DB::table('users')->insert(['id' => 1, 'name' => 'Test', 'money' => 100, 'active_login_token' => 'premium-settings-token', 'email_verified_at' => now()]);
        DB::table('monsters')->insert(['id' => 1]);
        DB::table('monster_on_locations')->insert(['id' => 1, 'monster_id' => 1]);
        DB::table('battle_details')->insert(['battle_id' => 1, 'location_monster_id' => 1]);

        $repository = \Mockery::mock(ChatMessageRepositoryInterface::class);
        $repository->shouldReceive('create')->andReturnUsing(function (array $data): ChatMessage {
            $this->messages[] = $data;

            return new ChatMessage($data);
        });
        $this->app->instance(ChatMessageRepositoryInterface::class, $repository);
    }

    public function test_third_level_auto_loots_items_and_chests_and_sends_private_item_messages(): void
    {
        $this->premium(3);
        $this->dropDefinition(1, 'resource', 3);
        $this->dropDefinition(2, 'chest', 2);
        $this->dropDefinition(3, 'resource', 25);
        DB::table('share_item_instant_rewards')->insert(['share_item_id' => 3, 'reward_type' => 'money']);
        DB::table('monster_on_locations')->insert(['id' => 2, 'monster_id' => 1]);
        DB::table('battle_details')->insert(['battle_id' => 1, 'location_monster_id' => 2]);
        $this->drop();
        $this->assertDatabaseCount('item_on_locations', 0);
        $this->assertDatabaseCount('items', 2);
        $this->assertSame(6, DB::table('backpacks')->join('items', 'items.id', '=', 'backpacks.item_id')->where('share_item_id', 1)->sum('count'));
        $this->assertSame(4, DB::table('backpacks')->join('items', 'items.id', '=', 'backpacks.item_id')->where('share_item_id', 2)->sum('count'));
        $this->assertSame(150, DB::table('users')->where('id', 1)->value('money'));
        $this->assertCount(3, $this->messages);
        foreach ($this->messages as $message) {
            $this->assertSame(1, $message['target_user_id']);
            $this->assertNull($message['user_id']);
            $this->assertSame('main', $message['channel']);
            $this->assertSame('loot', $message['type']);
        }
        $this->assertSame(['item_id' => 1, 'count' => 6], $this->messages[0]['translation_params']);
        $this->assertSame('chat.monster_auto_loot_money', $this->messages[2]['translation_key']);
    }

    public function test_lower_levels_and_expired_or_disabled_premium_keep_loot_on_ground(): void
    {
        $this->dropDefinition(1, 'resource', 3);
        foreach ([1, 2, 3, 4] as $scenario) {
            PremiumPeriod::query()->delete();
            $period = $this->premium(min($scenario, 3));
            if ($scenario === 3) {
                $period->update(['expires_at' => now()->subSecond()]);
            }
            if ($scenario === 4) {
                $level = PremiumLevel::findOrFail(3);
                $config = $level->bonuses;
                $config['_enabled']['auto_loot'] = false;
                $level->update(['bonuses' => $config]);
            }
            app(PremiumBonusService::class)->forget(1);
            $this->drop();
        }
        $this->assertDatabaseCount('item_on_locations', 4);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertSame([], $this->messages);
    }

    public function test_existing_direct_drop_and_ground_dungeon_scope_are_preserved_without_premium(): void
    {
        $this->dropDefinition(1, 'resource', 3);
        $this->dropDefinition(2, 'chest', 1);
        DB::table('share_items')->update(['drop_direct_to_backpack' => true]);
        $this->drop();
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseHas('item_on_locations', ['dungeon_session_id' => 99, 'interaction_type' => 'pickup']);
        $this->assertSame([], $this->messages);
    }

    public function test_active_battle_and_zero_drop_chance_do_not_grant_items_or_messages(): void
    {
        $this->premium(5);
        $this->dropDefinition(1, 'resource', 3);
        $this->drop(BattleStatus::ACTIVE);
        DB::table('monster_has_items')->update(['drop_chance' => 0]);
        $this->drop();
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertDatabaseCount('item_on_locations', 0);
        $this->assertSame([], $this->messages);
    }

    public function test_default_bonus_configuration_starts_at_three_and_preserves_other_settings(): void
    {
        foreach (range(1, 5) as $number) {
            $this->assertSame($number >= 3, PremiumLevel::findOrFail($number)->bonusEnabled(PremiumBonus::AUTO_LOOT));
            $this->assertSame((int) ($number >= 3), PremiumLevel::findOrFail($number)->bonuses['auto_loot']);
        }
        $level = PremiumLevel::findOrFail(3);
        $config = $level->bonuses;
        $config['_enabled']['auto_loot'] = false;
        $config['bag_slots'] = 77;
        $level->update(['bonuses' => $config]);
        (require database_path('migrations/2026_10_30_000001_add_premium_auto_loot_bonus.php'))->up();
        $this->assertFalse($level->fresh()->bonusEnabled(PremiumBonus::AUTO_LOOT));
        $this->assertSame(77, $level->fresh()->bonuses['bag_slots']);
    }

    public function test_settings_checkbox_disables_and_reenables_premium_loot_delivery(): void
    {
        $this->premium(3);
        $this->dropDefinition(1, 'resource', 3);
        $this->signInSettings();
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'auto_loot_enabled' => false])
            ->assertOk()->assertJsonPath('settings.auto_loot_enabled', false);
        $this->drop();
        $this->assertDatabaseCount('item_on_locations', 1);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertSame([], $this->messages);
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'auto_loot_enabled' => true])
            ->assertOk()->assertJsonPath('settings.auto_loot_enabled', true);
        $this->drop();
        $this->assertDatabaseCount('item_on_locations', 1);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertCount(1, $this->messages);
        $this->assertTrue(UserGameSetting::first()->settings['auto_loot_enabled']);
    }

    public function test_checkbox_is_enabled_for_all_premium_levels_from_three_and_disabled_after_expiry(): void
    {
        $this->signInSettings();
        $period = $this->premium(3);
        foreach ([3, 4, 5] as $level) {
            $period->update(['diamond_cents' => $level * 1000]);
            app(PremiumBonusService::class)->forget(1);
            $response = $this->get(route('settings'))->assertOk()->assertViewHas('autoLootAvailable', true);
            $checkbox = $this->checkbox($response->getContent());
            $this->assertFalse($checkbox->hasAttribute('disabled'));
            $this->assertTrue($checkbox->hasAttribute('checked'));
        }
        $period->update(['expires_at' => now()->subSecond()]);
        app(PremiumBonusService::class)->forget(1);
        $response = $this->get(route('settings'))->assertOk()->assertViewHas('autoLootAvailable', false);
        $checkbox = $this->checkbox($response->getContent());
        $this->assertTrue($checkbox->hasAttribute('disabled'));
        $this->assertFalse($checkbox->hasAttribute('checked'));
        $this->assertDatabaseCount('user_game_settings', 0);
    }

    public function test_missing_lower_expired_and_admin_disabled_premium_cannot_enable_auto_loot_by_request(): void
    {
        $this->signInSettings();
        foreach (['none', 'one', 'two', 'expired', 'disabled'] as $scenario) {
            PremiumPeriod::query()->delete();
            if ($scenario !== 'none') {
                $period = $this->premium(match ($scenario) {
                    'one' => 1, 'two' => 2, default => 3
                });
                if ($scenario === 'expired') {
                    $period->update(['expires_at' => now()->subSecond()]);
                }
                if ($scenario === 'disabled') {
                    $level = PremiumLevel::findOrFail(3);
                    $bonuses = $level->bonuses;
                    $bonuses['_enabled']['auto_loot'] = false;
                    $level->update(['bonuses' => $bonuses]);
                }
            }
            app(PremiumBonusService::class)->forget(1);
            $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'auto_loot_enabled' => true])
                ->assertUnprocessable()->assertJsonValidationErrors('auto_loot_enabled');
        }
        $this->assertDatabaseCount('user_game_settings', 0);
    }

    public function test_stored_enabled_preference_does_not_bypass_expired_premium_or_level_gate(): void
    {
        $period = $this->premium(3);
        UserGameSetting::create(['user_id' => 1, 'settings' => ['auto_loot_enabled' => true]]);
        $this->dropDefinition(1, 'resource', 3);
        $period->update(['expires_at' => now()->subSecond()]);
        app(PremiumBonusService::class)->forget(1);
        $this->drop();
        $level = PremiumLevel::findOrFail(2);
        $bonuses = $level->bonuses;
        $bonuses['auto_loot'] = 1;
        $bonuses['_enabled']['auto_loot'] = true;
        $level->update(['bonuses' => $bonuses]);
        $period->update(['expires_at' => now()->addDay(), 'diamond_cents' => 2000]);
        app(PremiumBonusService::class)->forget(1);
        $this->drop();
        $this->assertDatabaseCount('item_on_locations', 2);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertSame([], $this->messages);
    }

    private function signInSettings(): void
    {
        $this->actingAs(User::without('player')->findOrFail(1))->withSession([
            '_token' => 'settings-token', ActivateLoginSession::SESSION_KEY => 'premium-settings-token',
        ]);
    }

    public function test_each_monster_rewards_its_top_damage_dealer_and_only_their_premium_applies(): void
    {
        $this->premium(3); // Игрок 1 добивает бой, но не получает добычу игрока 2.
        DB::table('users')->insert(['id' => 2, 'name' => 'Contributor', 'money' => 0]);
        $this->dropDefinition(1, 'resource', 1);
        DB::table('monster_on_locations')->insert(['id' => 2, 'monster_id' => 1]);
        DB::table('battle_details')->insert(['battle_id' => 1, 'location_monster_id' => 2]);
        DB::table('battle_monster_damage')->insert([
            ['battle_id' => 1, 'location_monster_id' => 1, 'user_id' => 2, 'damage' => 90],
            ['battle_id' => 1, 'location_monster_id' => 1, 'user_id' => 1, 'damage' => 10],
            ['battle_id' => 1, 'location_monster_id' => 2, 'user_id' => 1, 'damage' => 60],
            ['battle_id' => 1, 'location_monster_id' => 2, 'user_id' => 2, 'damage' => 40],
        ]);

        $this->drop();

        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 1]);
        $this->assertDatabaseMissing('backpacks', ['user_id' => 2]);
        $ground = DB::table('item_on_locations')->sole();
        $this->assertSame(2, $ground->loot_owner_id);
        $this->assertEqualsWithDelta(now()->addMinute()->timestamp, strtotime($ground->loot_reserved_until), 1);
        $this->assertCount(1, $this->messages);
        $this->assertSame(1, $this->messages[0]['target_user_id']);
    }

    public function test_effective_damage_excludes_overkill_and_zero_hits_and_breaks_ties_by_first_reached_total(): void
    {
        $service = app(MonsterLootService::class);
        $fallback = (new User)->forceFill(['id' => 1]);
        DB::table('users')->insert(['id' => 2, 'name' => 'Contributor']);
        $monster = (new MonsterOnLocation)->forceFill(['id' => 1, 'hp_now' => 100]);
        $service->damage($monster, 50, 2, 1);
        $service->damage($monster, 0, 1, 1);
        $service->damage($monster, 500, 1, 1);

        $this->assertSame(0, $monster->hp_now);
        $this->assertDatabaseCount('battle_monster_damage', 2);
        $this->assertSame(100, (int) DB::table('battle_monster_damage')->sum('damage'));
        $this->assertSame(2, $service->recipient(1, 1, $fallback)->id);
        $this->assertSame($fallback, $service->recipient(2, 1, $fallback));
    }

    public function test_clan_boss_damage_keeps_the_existing_clan_reward_distribution(): void
    {
        $monster = (new MonsterOnLocation)->forceFill(['id' => 1, 'hp_now' => 100, 'clan_boss_stage_state_id' => 1]);
        app(MonsterLootService::class)->damage($monster, 20, 1, 1);
        $this->assertSame(80, $monster->hp_now);
        $this->assertDatabaseCount('battle_monster_damage', 0);
    }

    public function test_monster_coins_are_paid_once_to_the_top_damage_dealer(): void
    {
        Schema::create('map_influences', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('map_id');
            $table->unsignedInteger('influence');
        });
        Schema::create('map_influence_levels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->unsignedInteger('required_influence');
            $table->boolean('is_active');
        });
        Schema::table('monster_on_locations', function (Blueprint $table): void {
            $table->boolean('is_drop_money')->default(false);
            $table->timestamps();
        });
        DB::table('users')->insert(['id' => 2, 'name' => 'Contributor', 'money' => 0]);
        DB::table('battle_monster_damage')->insert([
            ['battle_id' => 1, 'location_monster_id' => 1, 'user_id' => 2, 'damage' => 90],
            ['battle_id' => 1, 'location_monster_id' => 1, 'user_id' => 1, 'damage' => 10],
        ]);
        $recipient = app(MonsterLootService::class)->recipient(1, 1, User::without('player')->findOrFail(1));
        $monster = MonsterOnLocation::findOrFail(1);
        $monster->setRelation('monster', (new Monster)->forceFill(['name' => 'Тест', 'min_money' => 50, 'max_money' => 50]));
        $monster->setRelation('location', (new Location)->forceFill(['map_id' => 10]));

        $service = app(DropService::class);
        $result = new AttackResultDTO;
        $service->dropMoney($recipient, $monster, $result);
        $service->dropMoney($recipient, $monster, $result);

        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 50]);
    }

    private function checkbox(string $html): \DOMElement
    {
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $checkbox = (new \DOMXPath($document))->query('//input[@id="setting-auto_loot_enabled"]')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $checkbox);

        return $checkbox;
    }

    private function premium(int $level): PremiumPeriod
    {
        return PremiumPeriod::create(['user_id' => 1, 'diamond_cents' => $level * 1000, 'started_at' => now()->subHour(), 'expires_at' => now()->addDay()]);
    }

    private function dropDefinition(int $id, string $type, int $count): void
    {
        DB::table('share_items')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Предмет '.$id, 'uk' => 'ПредметУК '.$id]), 'type' => $type]);
        DB::table('monster_has_items')->insert(['monster_id' => 1, 'share_item_id' => $id, 'min_count' => $count, 'max_count' => $count]);
    }

    private function drop(BattleStatus $status = BattleStatus::FINISH): void
    {
        $user = (new User)->forceFill(['id' => 1, 'lvl' => 10, 'money' => 100])->setRelation('player', (new Player)->forceFill(['id' => 1]));
        app(DropService::class)->dropItemsFromMonsters(
            (new Battle)->forceFill(['id' => 1, 'status' => $status]),
            (new Location)->forceFill(['id' => 10]),
            $user,
            dungeonSessionId: 99,
        );
    }
}
