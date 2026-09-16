<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Item;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\Item\Domain\Services\ItemRequirementService;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Player\Application\Services\HotbarService;
use App\Modules\Player\Domain\Services\PlayerInjuryService;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChestSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();
    }

    public function test_opening_regular_chest_twice_does_not_generate_loot_twice(): void
    {
        $this->seedChest(100, 10, true);
        DB::table('share_items')->insert(['id' => 20, 'name' => 'Награда', 'type' => 'resource', 'is_stackable' => false]);
        DB::table('share_item_has_items')->insert([
            'parent_item_id' => 10,
            'share_item_id' => 20,
            'min_count' => 1,
            'max_count' => 1,
            'drop_chance' => 100,
        ]);

        $service = $this->service();
        $user = $this->user(1);

        $this->assertSame(100, $service->openChest($user, 100));
        $this->assertSame(100, $service->openChest($user, 100));
        $this->assertSame(1, DB::table('item_in_chest')->where('chest_id', 100)->count());
    }

    public function test_loot_must_belong_to_accessible_chest_and_can_only_be_claimed_once(): void
    {
        $this->seedChest(100, 10, true, true);
        $this->seedChest(101, 10, true, true);
        DB::table('share_items')->insert(['id' => 20, 'name' => 'Награда', 'type' => 'resource', 'is_stackable' => false]);
        DB::table('items')->insert(['id' => 200, 'share_item_id' => 20, 'is_open' => false]);
        DB::table('item_in_chest')->insert(['chest_id' => 101, 'item_id' => 200, 'count' => 1]);

        $service = $this->service();
        $user = $this->user(1);

        $this->assertSame('Кто-то уже поднял этот предмет...', $service->pickUpFromChest($user, 100, 200));
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 200]);

        $service->pickUpFromChest($user, 101, 200);
        $service->pickUpFromChest($user, 101, 200);

        $this->assertSame(1, DB::table('backpacks')->where('user_id', 1)->where('item_id', 200)->count());
        $this->assertDatabaseMissing('item_in_chest', ['chest_id' => 101, 'item_id' => 200]);
    }

    public function test_player_cannot_open_chest_from_another_inventory(): void
    {
        $this->seedChest(100, 10, false);

        $this->assertNull($this->service()->openChest($this->user(1), 100));
    }

    private function seedChest(int $itemId, int $shareItemId, bool $owned, bool $opened = false): void
    {
        if (! DB::table('share_items')->where('id', $shareItemId)->exists()) {
            DB::table('share_items')->insert(['id' => $shareItemId, 'name' => 'Сундук', 'type' => 'chest', 'is_stackable' => false]);
        }
        DB::table('items')->insert(['id' => $itemId, 'share_item_id' => $shareItemId, 'is_open' => $opened]);
        if ($owned) {
            DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => $itemId, 'equipped' => false, 'count' => 1]);
        } else {
            DB::table('backpacks')->insert(['user_id' => 2, 'item_id' => $itemId, 'equipped' => false, 'count' => 1]);
        }
    }

    private function user(int $id): User
    {
        $user = new User;
        $user->id = $id;

        return $user;
    }

    private function service(): ItemService
    {
        return new ItemService(
            $this->createMock(BackpackService::class),
            $this->createMock(HotbarService::class),
            new PlayerInjuryService($this->createMock(RandomizerInterface::class)),
            $this->createMock(ItemRequirementService::class),
            $this->createMock(QuestProgressService::class),
            $this->createMock(ItemActionLogger::class),
        );
    }

    private function createTables(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_stackable')->default(false);
            $table->timestamps();
        });
        Schema::create('share_item_lock_configs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedSmallInteger('lock_required_skill')->default(0);
            $table->timestamps();
        });
        Schema::create('share_item_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('min_count');
            $table->integer('max_count');
            $table->integer('drop_chance');
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->timestamps();
        });
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count')->default(1);
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('upgrade_lvl')->default(0);
            $table->integer('upgrade_pity')->default(0);
            $table->integer('upgrade_fail_streak')->default(0);
            $table->integer('socket_count')->default(0);
            $table->integer('rune_slot_count')->default(0);
            $table->integer('additional_attack')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_open')->default(false);
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
        Schema::create('item_in_chest', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('chest_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('count')->default(1);
            $table->timestamps();
        });
    }
}
