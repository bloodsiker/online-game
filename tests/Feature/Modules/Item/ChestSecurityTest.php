<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Item;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Item\Application\Mappers\PickupItemsPageViewMapper;
use App\Modules\Item\Domain\Services\InstantItemRewardService;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\Item\Domain\Services\ItemRequirementService;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Location\Application\Mappers\TakeItemsPageViewMapper;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
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

    public function test_instant_money_reward_is_credited_without_entering_backpack(): void
    {
        $this->seedChest(100, 10, true, true);
        DB::table('users')->insert(['id' => 1, 'money' => 100]);
        DB::table('share_items')->insert([
            'id' => 20,
            'name' => 'Горстка монет',
            'type' => 'misc',
            'image' => '/img/bank_stock/new_coins.gif',
            'is_stackable' => false,
        ]);
        DB::table('share_item_instant_rewards')->insert([
            'share_item_id' => 20,
            'reward_type' => 'money',
        ]);
        DB::table('items')->insert(['id' => 200, 'share_item_id' => 20, 'is_open' => false]);
        DB::table('item_in_chest')->insert(['chest_id' => 100, 'item_id' => 200, 'count' => 275]);

        $message = $this->service()->pickUpFromChest(User::query()->findOrFail(1), 100, 200);

        $this->assertSame('Вы получили <b>275</b> монет.', $message);
        $this->assertSame(375, (int) DB::table('users')->where('id', 1)->value('money'));
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 200]);
        $this->assertDatabaseMissing('items', ['id' => 200]);
        $this->assertDatabaseMissing('item_in_chest', ['chest_id' => 100, 'item_id' => 200]);
    }

    public function test_claiming_all_after_lockpicking_returns_money_loot_and_credits_balance(): void
    {
        $this->seedChest(100, 10, true, true);
        DB::table('users')->insert(['id' => 1, 'money' => 0]);
        DB::table('share_items')->insert([
            'id' => 20,
            'name' => 'Горстка монет',
            'type' => 'misc',
            'image' => '/img/bank_stock/new_coins.gif',
            'is_stackable' => false,
        ]);
        DB::table('share_item_instant_rewards')->insert([
            'share_item_id' => 20,
            'reward_type' => 'money',
        ]);
        DB::table('items')->insert(['id' => 200, 'share_item_id' => 20, 'is_open' => false]);
        DB::table('item_in_chest')->insert(['chest_id' => 100, 'item_id' => 200, 'count' => 1_250]);

        $loot = $this->service()->claimAllChestContents(
            User::query()->findOrFail(1),
            Item::query()->findOrFail(100),
        );

        $this->assertSame('money', $loot[0]['reward_type']);
        $this->assertSame(1_250, $loot[0]['count']);
        $this->assertSame(1_250, (int) DB::table('users')->where('id', 1)->value('money'));
        $this->assertDatabaseMissing('items', ['id' => 200]);
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 200]);
    }

    public function test_monster_drop_chest_must_be_picked_up_before_it_can_be_opened(): void
    {
        DB::table('share_items')->insert([
            'id' => 10,
            'name' => 'Трофейная шкатулка',
            'type' => 'chest',
            'is_stackable' => false,
        ]);
        DB::table('items')->insert(['id' => 100, 'share_item_id' => 10, 'is_open' => false]);
        DB::table('item_on_locations')->insert([
            'item_id' => 100,
            'location_id' => 6,
            'interaction_type' => 'pickup',
        ]);
        $user = $this->userOnLocation(1, 6);

        $this->assertNull($this->service()->openChest($user, 100));

        $message = $this->service()->pickUpFromLocation($user, 100);

        $this->assertSame('Вы подняли предмет <b>"Трофейная шкатулка"</b>...', $message);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 100]);
        $this->assertDatabaseMissing('item_on_locations', ['item_id' => 100]);
    }

    public function test_location_chest_can_be_opened_but_cannot_be_picked_up(): void
    {
        DB::table('share_items')->insert([
            'id' => 10,
            'name' => 'Сундук на локации',
            'type' => 'chest',
            'is_stackable' => false,
        ]);
        DB::table('items')->insert(['id' => 100, 'share_item_id' => 10, 'is_open' => false]);
        DB::table('item_on_locations')->insert([
            'item_id' => 100,
            'location_id' => 6,
            'interaction_type' => 'open_here',
        ]);
        $user = $this->userOnLocation(1, 6);

        $message = $this->service()->pickUpFromLocation($user, 100);

        $this->assertSame('Этот сундук нужно открыть прямо на локации.', $message);
        $this->assertDatabaseMissing('backpacks', ['user_id' => 1, 'item_id' => 100]);
        $this->assertDatabaseHas('item_on_locations', ['item_id' => 100]);
        $this->assertSame(100, $this->service()->openChest($user, 100));
        $this->assertTrue((bool) Item::query()->findOrFail(100)->is_open);
    }

    public function test_location_list_shows_pickup_only_for_portable_chest(): void
    {
        DB::table('share_items')->insert([
            'id' => 10,
            'name' => 'Трофейная шкатулка',
            'type' => 'chest',
            'is_stackable' => false,
        ]);
        DB::table('items')->insert(['id' => 100, 'share_item_id' => 10, 'is_open' => false]);
        DB::table('item_on_locations')->insert([
            'item_id' => 100,
            'location_id' => 6,
            'interaction_type' => 'pickup',
        ]);

        $page = app(PickupItemsPageViewMapper::class)->map(
            ItemOnLocation::query()->with(['item', 'item.itemInfo.lockConfig'])->get(),
            '',
            0,
        );

        $this->assertSame('Поднять', $page->items[0]->actionLabel);
        $this->assertSame(route('items.pick_up', ['id' => 100]), $page->items[0]->actionUrl);
        $this->assertNull($page->items[0]->lockpickingUrl);

        $takeItemsPage = app(TakeItemsPageViewMapper::class)->map(
            ItemOnLocation::query()->with(['item', 'item.itemInfo.lockConfig'])->get(),
        );

        $this->assertSame('Поднять', $takeItemsPage->items[0]->actionLabel);
        $this->assertSame(route('items.pick_up', ['id' => 100]), $takeItemsPage->items[0]->actionUrl);
        $this->assertNull($takeItemsPage->items[0]->lockpickingUrl);
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

    private function userOnLocation(int $id, int $locationId): User
    {
        $user = $this->user($id);
        $user->location_id = $locationId;
        $location = new Location;
        $location->id = $locationId;
        $location->dungeon_id = null;
        $user->setRelation('currentLocation', $location);

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
            new InstantItemRewardService,
        );
    }

    private function createTables(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('image')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->timestamps();
        });
        Schema::create('share_item_instant_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->string('reward_type');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('money')->default(0);
            $table->timestamps();
        });
        Schema::create('share_item_lock_configs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedSmallInteger('lock_required_skill')->default(0);
            $table->unsignedTinyInteger('minimum_success_chance_percent')->default(5);
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
        Schema::create('item_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('location_id');
            $table->integer('count')->default(1);
            $table->string('interaction_type')->default('pickup');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
}
