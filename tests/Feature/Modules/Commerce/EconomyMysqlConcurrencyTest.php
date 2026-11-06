<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesEconomyFixture;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class EconomyMysqlConcurrencyTest extends TestCase
{
    use CreatesEconomyFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('ECONOMY_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires an explicitly created isolated MySQL database named codex_economy_race_*.');
        }
        // Refuse game databases before any schema or fixture changes.
        $this->assertMatchesRegularExpression('/^codex_economy_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        if (! Schema::hasTable('users')) {
            $this->createEconomyTables();
        }
        if (! Schema::hasIndex('shop_carts', 'shop_cart_user_offer_unique')) {
            Schema::table('shop_carts', fn ($table) => $table->unique(['user_id', 'shop_item_id'], 'shop_cart_user_offer_unique'));
        }
        foreach ([
            'shop_purchase_logs', 'item_action_logs', 'clan_tax_payments', 'clan_treasury_logs',
            'clan_warehouse_logs', 'clan_warehouses', 'clan_logs', 'clan_join_requests', 'clan_members',
            'clan_roles', 'clans', 'shop_carts', 'shop_item_requirements', 'shop_items', 'backpacks',
            'items', 'share_recipes', 'share_items', 'structures', 'locations', 'users',
        ] as $table) {
            DB::table($table)->delete();
        }
        $this->seedEconomyFixture();
    }

    private function race(array $first, ?array $second = null, ?callable $holdLocks = null): array
    {
        return $this->runConcurrentRequests('tests/Support/economy-race-worker.php', $this->database, 'ECONOMY_RACE_TEST_DATABASE', $first, $second, $holdLocks);
    }

    private function successes(array $results): int
    {
        return count(array_filter($results, fn ($result) => $result['ok']));
    }

    public function test_parallel_cart_additions_create_one_line_with_the_total_quantity(): void
    {
        $this->assertSame(2, $this->successes($this->race(['cart_add', 1, 3, 1], ['cart_add', 1, 4, 1])));
        $this->assertDatabaseCount('shop_carts', 1);
        $this->assertDatabaseHas('shop_carts', ['user_id' => 1, 'shop_item_id' => 1, 'quantity' => 7]);
    }

    public function test_cart_addition_cannot_be_erased_by_concurrent_checkout(): void
    {
        $this->seedEconomyCart(quantity: 1);
        $this->assertSame(2, $this->successes($this->race(['cart_add', 1, 2, 1], ['purchase', 1, '', ''])));
        $purchased = (int) DB::table('backpacks')->where('user_id', 1)->sum('count') - 10;
        $remaining = (int) DB::table('shop_carts')->where('user_id', 1)->sum('quantity');
        $this->assertSame(3, $purchased + $remaining);
        $this->assertSame(100 - 20 * $purchased, (int) DB::table('users')->where('id', 1)->value('money'));
    }

    public function test_cart_update_waits_for_the_same_user_lock_as_checkout(): void
    {
        $this->seedEconomyCart(quantity: 1);
        $results = $this->race(['cart_update', 1, 3, 1], ['purchase', 1, '', '']);
        $this->assertTrue($results[1]['ok']);
        $purchased = (int) DB::table('backpacks')->where('user_id', 1)->sum('count') - 10;
        $this->assertContains($purchased, [1, 3]);
        $this->assertSame(100 - 20 * $purchased, (int) DB::table('users')->where('id', 1)->value('money'));
        $this->assertDatabaseCount('shop_carts', 0);
    }

    public function test_cart_clear_and_addition_do_not_bypass_the_user_lock(): void
    {
        $this->seedEconomyCart(quantity: 1);
        $this->assertSame(2, $this->successes($this->race(['cart_clear', 1, 2, ''], ['cart_add', 1, 2, 1])));
        $this->assertContains((int) DB::table('shop_carts')->sum('quantity'), [0, 2]);
    }

    public function test_parallel_deposits_cannot_overdraw_one_wallet(): void
    {
        $this->assertSame(1, $this->successes($this->race(['deposit', 1, 80, ''])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 20]);
        $this->assertDatabaseHas('clans', ['treasury' => 180]);
        $this->assertDatabaseCount('clan_treasury_logs', 1);
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_two_members_cannot_overdraw_the_same_treasury(): void
    {
        $this->assertSame(1, $this->successes($this->race(['withdraw', 1, 80, ''], ['withdraw', 2, 80, ''])));
        $this->assertDatabaseHas('clans', ['treasury' => 20]);
        $this->assertSame(280, (int) DB::table('users')->whereIn('id', [1, 2])->sum('money'));
        $this->assertDatabaseCount('clan_treasury_logs', 1);
    }

    public function test_deposit_and_withdrawal_use_the_same_lock_order(): void
    {
        $this->assertSame(2, $this->successes($this->race(['deposit', 1, 40, ''], ['withdraw', 1, 40, ''])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('clans', ['treasury' => 100]);
        $this->assertDatabaseCount('clan_treasury_logs', 2);
    }

    public function test_parallel_full_sales_pay_only_once(): void
    {
        $this->assertSame(1, $this->successes($this->race(['sell', 1, 10, 1])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 200]);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertDatabaseCount('items', 0);
        $this->assertDatabaseCount('item_action_logs', 1);
    }

    public function test_parallel_partial_sales_sell_exactly_the_remaining_quantity(): void
    {
        $this->assertSame(2, $this->successes($this->race(['sell', 1, 7, 1])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 200]);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertSame(10, (int) DB::table('item_action_logs')->sum('count'));
        $this->assertSame(100, (int) DB::table('item_action_logs')->sum('money'));
    }

    public function test_sale_and_normal_purchase_preserve_both_inventory_and_money(): void
    {
        $this->seedEconomyCart();
        $this->assertSame(2, $this->successes($this->race(['sell', 1, 5, 1], ['purchase', 1, '', ''])));
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 7]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 110]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseCount('shop_purchase_logs', 1);
        $this->assertDatabaseCount('shop_carts', 0);
    }

    public function test_sale_and_premium_purchase_preserve_both_inventory_and_currencies(): void
    {
        $this->seedEconomyCart(premium: true);
        $this->assertSame(2, $this->successes($this->race(['sell', 1, 5, 1], ['premium', 1, '', ''])));
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 7]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 60]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseCount('shop_purchase_logs', 1);
    }

    public function test_buying_while_selling_the_entire_old_stack_keeps_the_new_items(): void
    {
        $this->seedEconomyCart();
        $this->assertSame(2, $this->successes($this->race(['sell', 1, 10, 1], ['purchase', 1, '', ''])));
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 2]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 160]);
    }

    public function test_two_shop_types_merge_into_one_stack_without_losing_either_purchase(): void
    {
        $this->seedEconomyCart();
        $this->seedEconomyCart(premium: true);
        $this->assertSame(2, $this->successes($this->race(['purchase', 1, '', ''], ['premium', 1, '', ''])));
        $this->assertDatabaseHas('backpacks', ['count' => 14]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 60, 'diamond' => 60]);
        $this->assertDatabaseCount('shop_purchase_logs', 2);
    }

    public function test_stack_lock_reads_the_current_quantity_after_an_independent_inventory_update(): void
    {
        $this->seedEconomyCart();
        $this->seedEconomyCart(premium: true);
        $results = $this->race(['purchase', 1, '', ''], ['premium', 1, '', ''], holdLocks: function (): void {
            DB::table('backpacks')->where('id', 1)->lockForUpdate()->first();
            DB::table('backpacks')->where('id', 1)->decrement('count', 3);
        });
        $this->assertSame(2, $this->successes($results));
        $this->assertDatabaseHas('backpacks', ['count' => 11]);
        $this->assertDatabaseCount('backpacks', 1);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 60, 'diamond' => 60]);
    }
}
