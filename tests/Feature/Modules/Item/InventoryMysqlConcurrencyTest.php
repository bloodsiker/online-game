<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Item;

use App\Modules\Item\Domain\Services\ItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesInventoryFixture;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class InventoryMysqlConcurrencyTest extends TestCase
{
    use CreatesInventoryFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('INVENTORY_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires an explicitly created isolated MySQL database named codex_inventory_race_*.');
        }
        $this->assertMatchesRegularExpression('/^codex_inventory_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        if (! Schema::hasTable('users')) {
            $this->createInventoryTables();
        }
        foreach ([
            'item_on_locations', 'item_gems', 'item_runes', 'share_item_instant_rewards',
            'shop_purchase_logs', 'item_action_logs', 'clan_tax_payments', 'clan_treasury_logs',
            'clan_warehouse_logs', 'clan_warehouses', 'clan_logs', 'clan_join_requests', 'clan_members',
            'clan_roles', 'clans', 'shop_carts', 'shop_item_requirements', 'shop_items', 'backpacks',
            'items', 'share_recipes', 'share_items', 'structures', 'locations', 'users', 'players',
        ] as $table) {
            DB::table($table)->delete();
        }
        $this->seedInventoryFixture();
    }

    private function race(array $first, ?array $second = null): array
    {
        return $this->runConcurrentRequests('tests/Support/inventory-race-worker.php', $this->database, 'INVENTORY_RACE_TEST_DATABASE', $first, $second);
    }

    public function test_parallel_transfers_of_one_stack_have_only_one_recipient(): void
    {
        $results = $this->race(['transfer', 1, 1, 2], ['transfer', 1, 1, 3]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertContains((int) DB::table('backpacks')->value('user_id'), [2, 3]);
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count'));
        $this->assertDatabaseHas('item_action_logs', ['action' => 'give', 'count' => 10]);
        $this->assertDatabaseCount('item_action_logs', 1);
    }

    public function test_opposite_transfers_lock_players_in_the_same_order(): void
    {
        DB::table('items')->insert(['id' => 2, 'share_item_id' => 1]);
        DB::table('backpacks')->insert(['item_id' => 2, 'user_id' => 2, 'count' => 6]);
        $results = $this->race(['transfer', 1, 1, 2], ['transfer', 2, 2, 1]);
        $this->assertSame(2, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('backpacks', ['user_id' => 2, 'item_id' => 1, 'count' => 10]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => 2, 'count' => 6]);
    }

    public function test_parallel_full_drops_create_only_one_ground_placement(): void
    {
        $results = $this->race(['drop', 1, 1, 10]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertDatabaseCount('item_on_locations', 1);
        $this->assertDatabaseHas('item_on_locations', ['item_id' => 1, 'count' => 10, 'loot_owner_id' => null]);
    }

    public function test_parallel_partial_drops_cannot_discard_more_than_the_original_stack(): void
    {
        $this->race(['drop', 1, 1, 7]);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertSame(10, (int) DB::table('item_on_locations')->sum('count'));
        $this->assertSame(2, DB::table('item_on_locations')->distinct()->count('item_id'));
        $this->assertSame(10, (int) DB::table('item_action_logs')->sum('count'));
    }

    public function test_transfer_and_drop_cannot_both_move_the_same_stack(): void
    {
        $results = $this->race(['transfer', 1, 1, 2], ['drop', 1, 1, 10]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count') + (int) DB::table('item_on_locations')->sum('count'));
        $this->assertDatabaseCount('item_action_logs', 1);
    }

    public function test_two_players_can_pick_up_one_ground_stack_only_once(): void
    {
        app(ItemService::class)->drop($this->economyUser(), 1, 10);
        $this->race(['pickup', 2, 1, ''], ['pickup', 3, 1, '']);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseCount('item_on_locations', 0);
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count'));
    }

    public function test_drop_and_sale_conserve_quantity_and_pay_only_for_sold_items(): void
    {
        $this->race(['drop', 1, 1, 7], ['sell', 1, 1, 5]);
        $sold = (int) DB::table('item_action_logs')->where('action', 'sell')->sum('count');
        $ground = (int) DB::table('item_on_locations')->sum('count');
        $this->assertSame(10, $sold + $ground);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100 + $sold * 10]);
    }

    public function test_pickup_of_a_split_stack_cannot_remove_the_owners_remaining_items(): void
    {
        app(ItemService::class)->drop($this->economyUser(), 1, 4);
        $groundId = (int) DB::table('item_on_locations')->value('item_id');
        $this->race(['pickup', 2, $groundId, ''], ['drop', 1, 1, 6]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 2, 'count' => 4]);
        $this->assertDatabaseHas('item_on_locations', ['item_id' => 1, 'count' => 6]);
        $this->assertDatabaseCount('items', 2);
    }
}
