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
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('prev_location_id')->nullable();
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
            $table->integer('count_use')->default(0);
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
    }
}
