<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Item;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UseItemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();

        DB::table('races')->insert(['id' => 1, 'name' => 'Человек']);
        DB::table('locations')->insert(['id' => 10]);
        DB::table('players')->insert([
            'id' => 1,
            'user_id' => 1,
            'race_id' => 1,
            'hp_now' => 10,
            'hp_max' => 10,
            'mp_now' => 0,
            'mp_max' => 0,
            'lvl' => 1,
            'strength' => 1,
            'intuition' => 1,
            'agility' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'endurance' => 1,
            'min_dmg' => 0,
            'max_dmg' => 0,
            'free_stats' => 0,
        ]);
        DB::table('users')->insert([
            'id' => 1,
            'player_id' => 1,
            'name' => 'Игрок',
            'email' => 'player@example.test',
            'password' => 'password',
            'location_id' => 10,
        ]);

        $this->actingAs(User::findOrFail(1));
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_presence_pass_key_cannot_be_used_or_consumed_from_backpack(): void
    {
        $this->giveKey(324, 100);
        $this->createGate(324, 'presence_pass', false, 10, 11);

        $response = $this->postJson(route('items.use', 100));

        $response->assertUnprocessable()
            ->assertJson([
                'status' => 'error',
                'message' => 'Этот предмет нельзя использовать здесь.',
            ]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 100, 'count' => 1]);
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 10]);
    }

    public function test_teleport_use_key_moves_player_without_consuming_when_disabled(): void
    {
        $this->giveKey(400, 101);
        $this->createGate(400, 'teleport_use', false, 10, 11);

        $response = $this->postJson(route('items.use', 101));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'removed' => false,
                'count' => 1,
            ]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 101, 'count' => 1]);
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 11, 'prev_location_id' => 10]);
    }

    public function test_consumable_multi_use_key_loses_one_use_after_teleport(): void
    {
        $this->giveKey(401, 102, configuredUses: 2, remainingUses: 2);
        $this->createGate(401, 'teleport_use', true, 10, 11);

        $response = $this->postJson(route('items.use', 102));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'removed' => false,
                'count' => 1,
            ]);
        $this->assertDatabaseHas('items', ['id' => 102, 'count_use' => 1]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 102]);
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 11]);
    }

    public function test_consumable_single_use_key_is_removed_after_teleport(): void
    {
        $this->giveKey(402, 103);
        $this->createGate(402, 'teleport_use', true, 10, 11);

        $response = $this->postJson(route('items.use', 103));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'removed' => true,
                'count' => 0,
            ]);
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 103]);
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 11]);
    }

    public function test_item_applies_all_configured_buffs_and_is_consumed(): void
    {
        $this->giveKey(450, 104);
        DB::table('effects')->insert([
            ['id' => 1, 'name' => 'Сила медведя', 'slug' => 'bear_strength', 'type' => 'buff', 'stat_modifiers' => json_encode([['type' => 'strength', 'value' => 5]])],
            ['id' => 2, 'name' => 'Каменная кожа', 'slug' => 'stone_skin', 'type' => 'buff', 'stat_modifiers' => json_encode([['type' => 'armor', 'value' => 10]])],
        ]);
        DB::table('share_item_buffs')->insert([
            ['share_item_id' => 450, 'effect_id' => 1, 'duration_seconds' => 60],
            ['share_item_id' => 450, 'effect_id' => 2, 'duration_seconds' => 120],
        ]);

        $response = $this->postJson(route('items.use', 104));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'blessings')
            ->assertJsonPath('blessings.0.name', 'Сила медведя')
            ->assertJsonPath('blessings.1.name', 'Каменная кожа');
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 104]);
        $this->assertDatabaseHas('player_active_effects', ['player_id' => 1, 'effect_id' => 1]);
        $this->assertDatabaseHas('player_active_effects', ['player_id' => 1, 'effect_id' => 2]);
    }

    public function test_item_applies_configured_debuffs_to_selected_player_only(): void
    {
        $this->giveKey(451, 106);
        DB::table('players')->insert([
            'id' => 2, 'user_id' => 2, 'race_id' => 1, 'hp_now' => 10, 'hp_max' => 10, 'mp_now' => 0, 'mp_max' => 0,
            'lvl' => 1, 'strength' => 1, 'intuition' => 1, 'agility' => 1, 'wisdom' => 1, 'intelligence' => 1, 'endurance' => 1,
            'min_dmg' => 0, 'max_dmg' => 0, 'free_stats' => 0,
        ]);
        DB::table('users')->insert([
            'id' => 2, 'player_id' => 2, 'name' => 'Цель', 'email' => 'target@example.test', 'password' => 'password',
            'location_id' => 10, 'last_online_at' => now(),
        ]);
        DB::table('effects')->insert([
            'id' => 3, 'name' => 'Отравление', 'slug' => 'item_poison', 'type' => 'debuff', 'active_type' => 'poison',
        ]);
        DB::table('share_item_debuffs')->insert([
            'share_item_id' => 451, 'effect_id' => 3, 'duration_seconds' => 60,
        ]);

        $response = $this->postJson(route('items.use', 106), ['target_player_id' => 2]);

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('player_active_effects', ['player_id' => 2, 'effect_id' => 3]);
        $this->assertDatabaseMissing('player_active_effects', ['player_id' => 1, 'effect_id' => 3]);
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 106]);
    }

    public function test_non_droppable_item_cannot_be_dropped_from_backpack(): void
    {
        $this->giveInventoryItem(500, 104, isDroppable: false);

        $response = $this->get(route('items.drop', ['id' => 104, 'c' => 1, 'qty' => 1]));

        $response->assertRedirect(route('backpack'))
            ->assertSessionHas('message', 'Этот предмет нельзя выбросить.');
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 104, 'count' => 1]);
        $this->assertDatabaseMissing('item_on_locations', ['item_id' => 104]);
    }

    public function test_droppable_item_can_be_dropped_from_backpack(): void
    {
        $this->giveInventoryItem(501, 105, isDroppable: true);

        $response = $this->get(route('items.drop', ['id' => 105, 'c' => 1, 'qty' => 1]));

        $response->assertRedirect(route('backpack'));
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 105]);
        $this->assertDatabaseHas('item_on_locations', [
            'location_id' => 10,
            'item_id' => 105,
            'count' => 1,
        ]);
    }

    private function giveKey(
        int $shareItemId,
        int $itemId,
        int $configuredUses = 0,
        int $remainingUses = 0,
    ): void {
        DB::table('share_items')->insert([
            'id' => $shareItemId,
            'type' => 'key',
            'name' => "Ключ {$shareItemId}",
            'count_use' => $configuredUses,
        ]);
        DB::table('items')->insert([
            'id' => $itemId,
            'share_item_id' => $shareItemId,
            'count_use' => $remainingUses,
        ]);
        DB::table('backpacks')->insert([
            'user_id' => 1,
            'item_id' => $itemId,
            'equipped' => false,
            'count' => 1,
        ]);
    }

    private function giveInventoryItem(int $shareItemId, int $itemId, bool $isDroppable): void
    {
        DB::table('share_items')->insert([
            'id' => $shareItemId,
            'type' => 'resource',
            'name' => "Предмет {$shareItemId}",
            'is_droppable' => $isDroppable,
        ]);
        DB::table('items')->insert([
            'id' => $itemId,
            'share_item_id' => $shareItemId,
        ]);
        DB::table('backpacks')->insert([
            'user_id' => 1,
            'item_id' => $itemId,
            'equipped' => false,
            'count' => 1,
        ]);
    }

    private function createGate(
        int $shareItemId,
        string $mode,
        bool $consumeItem,
        int $fromLocationId,
        int $toLocationId,
    ): void {
        DB::table('location_gates')->insert([
            'from_location_id' => $fromLocationId,
            'to_location_id' => $toLocationId,
            'share_item_id' => $shareItemId,
            'mode' => $mode,
            'consume_item' => $consumeItem,
        ]);
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
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->integer('mp_now');
            $table->integer('mp_max');
            $table->integer('lvl')->default(1);
            $table->integer('strength')->default(1);
            $table->integer('intuition')->default(1);
            $table->integer('agility')->default(1);
            $table->integer('wisdom')->default(1);
            $table->integer('intelligence')->default(1);
            $table->integer('endurance')->default(1);
            $table->integer('min_dmg')->default(0);
            $table->integer('max_dmg')->default(0);
            $table->integer('free_stats')->default(0);
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('prev_location_id')->nullable();
            $table->timestamp('last_online_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id')->nullable();
            $table->timestamps();
        });

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->integer('count_use')->default(0);
            $table->unsignedInteger('expire')->nullable();
            $table->boolean('is_droppable')->default(true);
            $table->timestamps();
        });

        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('upgrade_lvl')->default(0);
            $table->integer('count_use')->default(0);
            $table->timestamps();
        });

        Schema::create('item_action_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('share_item_id');
            $table->string('item_name');
            $table->integer('upgrade_lvl')->default(0);
            $table->string('action');
            $table->integer('count')->default(1);
            $table->integer('money')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->integer('count')->default(1);
            $table->timestamps();
        });

        Schema::create('item_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('location_id');
            $table->integer('count')->default(1);
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('location_gates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('from_location_id');
            $table->unsignedBigInteger('to_location_id');
            $table->unsignedBigInteger('share_item_id');
            $table->string('mode');
            $table->boolean('consume_item')->default(false);
            $table->string('button_label')->nullable();
            $table->timestamps();
        });

        Schema::create('share_item_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('effect_type');
            $table->integer('value');
            $table->string('value_type');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();
        });

        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->string('active_type')->nullable();
            $table->string('damage_scaling_type')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('chance')->default(0);
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stacks')->default(1);
            $table->integer('tick_interval')->default(1);
            $table->integer('value_per_tick')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_dispellable')->default(true);
            $table->timestamps();
        });

        Schema::create('share_item_buffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('effect_id');
            $table->unsignedInteger('duration_seconds');
            $table->timestamps();
        });

        Schema::create('share_item_debuffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('effect_id');
            $table->unsignedInteger('duration_seconds');
            $table->timestamps();
        });

        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('applied_at');
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('next_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->float('tick_remainder')->default(0);
            $table->timestamps();
        });

        Schema::create('player_item_buffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('effect_type');
            $table->integer('value');
            $table->string('value_type');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('player_equipments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->timestamps();
        });

        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_passive')->default(false);
            $table->json('effects')->nullable();
            $table->timestamps();
        });

        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance')->default(100);
            $table->integer('duration_seconds')->default(0);
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->timestamps();
        });
    }
}
