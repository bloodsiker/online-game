<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Item;

use App\Modules\Item\Application\Mappers\HandOverPageViewMapper;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\Support\CreatesInventoryFixture;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
{
    use CreatesInventoryFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createInventoryTables();
        $this->seedInventoryFixture();
    }

    public function test_full_stack_transfer_preserves_quantity_instance_and_attributes_and_cannot_repeat(): void
    {
        DB::table('items')->where('id', 1)->update(['upgrade_lvl' => 5, 'count_use' => 3, 'expires_at' => now()->addDay()]);
        $item = Item::findOrFail(1);
        $service = app(ItemService::class);
        $this->assertNull($service->handOver($this->economyUser(), $item, $this->economyUser(2)));
        $this->assertNotNull($service->handOver($this->economyUser(), $item, $this->economyUser(2)));
        $this->assertDatabaseHas('backpacks', ['id' => 1, 'user_id' => 2, 'count' => 10]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseCount('items', 1);
        $this->assertDatabaseHas('items', ['id' => 1, 'upgrade_lvl' => 5, 'count_use' => 3]);
        $this->assertDatabaseHas('item_action_logs', ['action' => 'give', 'count' => 10, 'target_user_id' => 2]);
    }

    public function test_transfer_rechecks_current_location_and_give_flag_instead_of_stale_models(): void
    {
        $item = Item::findOrFail(1);
        $recipient = $this->economyUser(2);
        DB::table('users')->where('id', 2)->update(['location_id' => 2]);
        $service = app(ItemService::class);
        $this->assertNotNull($service->handOver($this->economyUser(), $item, $recipient));
        DB::table('users')->where('id', 2)->update(['location_id' => 1]);
        DB::table('share_items')->update(['is_give' => false]);
        $this->assertNotNull($service->handOver($this->economyUser(), $item, $recipient));
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 10]);
        $this->assertDatabaseCount('item_action_logs', 0);
    }

    public function test_self_transfer_foreign_item_and_equipped_item_are_rejected(): void
    {
        $service = app(ItemService::class);
        $item = Item::findOrFail(1);
        $this->assertNotNull($service->handOver($this->economyUser(), $item, $this->economyUser()));
        $this->assertNotNull($service->handOver($this->economyUser(2), $item, $this->economyUser(3)));
        DB::table('backpacks')->update(['equipped' => 1]);
        $this->assertNotNull($service->handOver($this->economyUser(), $item, $this->economyUser(2)));
        $this->assertNotNull($service->drop($this->economyUser(), 1, 2));
        $this->assertDatabaseCount('item_action_logs', 0);
    }

    public function test_partial_drop_creates_separate_instance_and_pickup_preserves_the_remainder(): void
    {
        DB::table('items')->update(['upgrade_lvl' => 2, 'count_use' => 3, 'expires_at' => now()->addDay()]);
        $service = app(ItemService::class);
        $this->assertNull($service->drop($this->economyUser(), 1, 4));
        $ground = DB::table('item_on_locations')->first();
        $this->assertNotSame(1, (int) $ground->item_id);
        $this->assertSame(4, (int) $ground->count);
        $this->assertNull($ground->loot_owner_id);
        $this->assertNull($ground->loot_reserved_until);
        $this->assertDatabaseHas('items', ['id' => $ground->item_id, 'upgrade_lvl' => 2, 'count_use' => 3]);
        $service->pickUpFromLocation($this->economyUser(2), (int) $ground->item_id);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 1, 'count' => 6]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 2, 'item_id' => $ground->item_id, 'count' => 4]);
        $this->assertDatabaseCount('item_on_locations', 0);
        $this->assertDatabaseCount('items', 2);
    }

    public function test_partial_drop_copies_gems_and_runes_of_legacy_stacked_equipment(): void
    {
        DB::table('items')->update(['socket_count' => 1, 'rune_slot_count' => 1]);
        DB::table('item_gems')->insert(['item_id' => 1, 'share_item_id' => 1]);
        DB::table('item_runes')->insert(['item_id' => 1, 'share_item_id' => 1]);
        $this->assertNull(app(ItemService::class)->drop($this->economyUser(), 1, 2));
        $id = DB::table('item_on_locations')->value('item_id');
        $this->assertDatabaseHas('item_gems', ['item_id' => $id]);
        $this->assertDatabaseHas('item_runes', ['item_id' => $id]);
        $this->assertDatabaseCount('item_gems', 2);
        $this->assertDatabaseCount('item_runes', 2);
    }

    public function test_legacy_shared_ground_instance_is_split_instead_of_creating_duplicate_placement(): void
    {
        DB::table('item_on_locations')->insert(['item_id' => 1, 'location_id' => 1, 'count' => 2]);
        $this->assertNull(app(ItemService::class)->drop($this->economyUser(), 1, 10));
        $this->assertSame(2, DB::table('item_on_locations')->distinct()->count('item_id'));
        $this->assertSame(12, (int) DB::table('item_on_locations')->sum('count'));
        $this->assertDatabaseCount('backpacks', 0);
    }

    public function test_map_destination_is_preserved_but_search_is_stopped_when_split_and_transferred(): void
    {
        Schema::create('treasure_hunts', function (Blueprint $table): void {
            $table->id();
            foreach (['map_item_id', 'user_id', 'target_map_id', 'target_location_id'] as $field) {
                $table->unsignedBigInteger($field);
            }
            $table->string('search_token')->nullable();
            foreach (['started_at', 'completes_at', 'expires_at', 'activated_at'] as $field) {
                $table->timestamp($field)->nullable();
            }
            $table->float('chance_snapshot')->nullable();
            $table->timestamps();
        });
        DB::table('share_items')->update(['type' => 'treasure_map']);
        DB::table('treasure_hunts')->insert([
            'map_item_id' => 1, 'user_id' => 1, 'target_map_id' => 6, 'target_location_id' => 25,
            'search_token' => 'active-search', 'started_at' => now(), 'completes_at' => now()->addMinute(),
        ]);
        $service = app(ItemService::class);
        $this->assertNull($service->drop($this->economyUser(), 1, 4));
        $copyId = (int) DB::table('item_on_locations')->value('item_id');
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => $copyId, 'target_location_id' => 25, 'search_token' => null]);
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => 1, 'search_token' => 'active-search']);
        $service->pickUpFromLocation($this->economyUser(2), $copyId);
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => $copyId, 'user_id' => 2, 'target_map_id' => 6]);
        $this->assertNull($service->handOver($this->economyUser(), Item::findOrFail(1), $this->economyUser(3)));
        $this->assertDatabaseHas('treasure_hunts', ['map_item_id' => 1, 'user_id' => 3, 'search_token' => null, 'target_location_id' => 25]);
    }

    public function test_repeated_full_drop_or_pickup_does_not_duplicate_the_stack(): void
    {
        $service = app(ItemService::class);
        $this->assertNull($service->drop($this->economyUser(), 1, 100));
        $this->assertNotNull($service->drop($this->economyUser(), 1, 100));
        $this->assertDatabaseCount('item_on_locations', 1);
        $message = $service->pickUpFromLocation($this->economyUser(2), 1);
        $this->assertSame('Вы подняли предмет <b>"Агат"</b>', $message);
        $service->pickUpFromLocation($this->economyUser(2), 1);
        $this->assertDatabaseHas('backpacks', ['user_id' => 2, 'count' => 10]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseCount('item_on_locations', 0);
    }

    public function test_invalid_drop_quantities_cannot_remove_items_and_location_is_refreshed(): void
    {
        $service = app(ItemService::class);
        $user = $this->economyUser();
        $user->load('currentLocation');
        foreach ([0, -1] as $amount) {
            $this->assertNotNull($service->drop($user, 1, $amount));
        }
        DB::table('users')->where('id', 1)->update(['location_id' => 2]);
        $this->assertNull($service->drop($user, 1, 2));
        $this->assertDatabaseHas('item_on_locations', ['location_id' => 2, 'count' => 2]);
    }

    public function test_quest_discard_is_destroyed_and_decreases_progress_atomically(): void
    {
        DB::table('share_items')->update(['type' => 'quest']);
        $progress = Mockery::mock(QuestProgressService::class);
        $progress->shouldReceive('decreaseCollectProgress')->once()->withArgs(fn ($player, $id, $qty) => $player->id === 1 && $id === 1 && $qty === 10);
        $this->app->instance(QuestProgressService::class, $progress);
        $this->assertNull(app(ItemService::class)->drop($this->economyUser(), 1, 10));
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertDatabaseCount('items', 0);
        $this->assertDatabaseCount('item_on_locations', 0);
    }

    public function test_failed_log_rolls_back_both_drop_and_transfer(): void
    {
        $logger = Mockery::mock(ItemActionLogger::class);
        $logger->shouldReceive('log')->twice()->andThrow(new RuntimeException('Audit failed'));
        $this->app->instance(ItemActionLogger::class, $logger);
        foreach (['drop', 'transfer'] as $action) {
            try {
                $service = app(ItemService::class);
                $action === 'drop' ? $service->drop($this->economyUser(), 1, 10)
                    : $service->handOver($this->economyUser(), Item::findOrFail(1), $this->economyUser(2));
                $this->fail('Audit failure should roll back the action.');
            } catch (RuntimeException $exception) {
                $this->assertSame('Audit failed', $exception->getMessage());
            }
            $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 10]);
            $this->assertDatabaseCount('items', 1);
            $this->assertDatabaseCount('item_on_locations', 0);
        }
    }

    public function test_failed_ground_insert_rolls_back_split_instance_count_and_action_log(): void
    {
        DB::statement("CREATE TRIGGER reject_ground_insert BEFORE INSERT ON item_on_locations BEGIN SELECT RAISE(ABORT, 'Ground insert failed'); END");
        $this->expectException(QueryException::class);
        try {
            app(ItemService::class)->drop($this->economyUser(), 1, 4);
        } finally {
            $this->assertDatabaseHas('backpacks', ['id' => 1, 'count' => 10]);
            $this->assertDatabaseCount('items', 1);
            $this->assertDatabaseCount('item_action_logs', 0);
            $this->assertDatabaseCount('item_on_locations', 0);
        }
    }

    public function test_duplicate_audit_detects_rows_but_never_changes_them(): void
    {
        $this->artisan('inventory:audit-duplicates')->assertExitCode(0);
        DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => 1, 'count' => 2]);
        DB::table('item_on_locations')->insert([
            ['item_id' => 1, 'location_id' => 1], ['item_id' => 1, 'location_id' => 1],
        ]);
        $this->artisan('inventory:audit-duplicates')->expectsOutputToContain('backpacks: 1 duplicate groups.')->assertExitCode(1);
        $this->assertDatabaseCount('backpacks', 2);
        $this->assertDatabaseCount('item_on_locations', 2);
    }

    public function test_drop_and_transfer_routes_require_post_and_form_contains_csrf(): void
    {
        foreach (['items.drop', 'items.hand_over_to_user'] as $route) {
            $this->assertSame(['POST'], Route::getRoutes()->getByName($route)->methods());
        }
        $page = app(HandOverPageViewMapper::class)->map(
            Item::findOrFail(1), collect([$this->economyUser(2)]), false, false, null,
        );
        $html = view('item::hand_over', ['page' => $page])->render();
        $this->assertStringContainsString('method="post"', $html);
        $this->assertStringContainsString('name="_token"', $html);
    }
}
