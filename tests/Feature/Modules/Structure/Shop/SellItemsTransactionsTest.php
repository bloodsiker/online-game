<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure\Shop;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\Structure\Shop\Application\UseCases\PurchaseCart;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use App\Modules\Structure\Shop\Domain\Contracts\ShopInventoryRepository;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\Support\CreatesEconomyFixture;
use Tests\TestCase;

class SellItemsTransactionsTest extends TestCase
{
    use CreatesEconomyFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createEconomyTables();
        $this->seedEconomyFixture();
    }

    public function test_repeated_sale_of_the_same_item_pays_once_and_updates_the_callers_display_balance(): void
    {
        $user = $this->economyUser();
        $this->assertTrue(app(SellItems::class)->execute($user, $this->sellSelection())->ok);
        $this->assertFalse(app(SellItems::class)->execute($user, $this->sellSelection())->ok);
        $this->assertSame(200, (int) $user->money);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 200]);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertDatabaseCount('items', 0);
        $this->assertDatabaseCount('item_action_logs', 1);
        $this->assertDatabaseHas('item_action_logs', ['count' => 10, 'money' => 100]);
    }

    public function test_partial_sales_clamp_to_the_current_remaining_count_without_replacing_other_money(): void
    {
        $user = $this->economyUser();
        DB::table('users')->where('id', 1)->increment('money', 40);
        $this->assertTrue(app(SellItems::class)->execute($user, $this->sellSelection(7))->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 210]);
        $this->assertDatabaseHas('backpacks', ['id' => 1, 'count' => 3]);
        $this->assertTrue(app(SellItems::class)->execute($user, $this->sellSelection(7))->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 240]);
        $this->assertSame(10, (int) DB::table('item_action_logs')->sum('count'));
        $this->assertDatabaseCount('items', 0);
    }

    public function test_foreign_equipped_and_unsellable_items_cannot_be_sold(): void
    {
        $this->assertFalse(app(SellItems::class)->execute($this->economyUser(2), $this->sellSelection())->ok);
        DB::table('backpacks')->update(['equipped' => 1]);
        $this->assertFalse(app(SellItems::class)->execute($this->economyUser(), $this->sellSelection())->ok);
        DB::table('backpacks')->update(['equipped' => 0]);
        DB::table('share_items')->update(['is_sell' => false]);
        $this->assertFalse(app(SellItems::class)->execute($this->economyUser(), $this->sellSelection())->ok);
        $this->assertDatabaseHas('backpacks', ['count' => 10]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseCount('item_action_logs', 0);
    }

    public function test_zero_negative_and_unselected_quantities_do_not_touch_inventory(): void
    {
        foreach ([[], [1 => ['selected' => 0, 'count' => 10]], $this->sellSelection(0), $this->sellSelection(-1)] as $selection) {
            $this->assertFalse(app(SellItems::class)->execute($this->economyUser(), $selection)->ok);
        }
        $this->assertDatabaseHas('backpacks', ['count' => 10]);
        $this->assertDatabaseCount('item_action_logs', 0);
    }

    public function test_wallet_overflow_preserves_the_entire_selection(): void
    {
        DB::table('users')->where('id', 1)->update(['money' => WalletBalanceLimits::MAX_BALANCE - 10]);
        $this->assertFalse(app(SellItems::class)->execute($this->economyUser(), $this->sellSelection())->ok);
        $this->assertDatabaseHas('backpacks', ['count' => 10]);
        $this->assertDatabaseCount('item_action_logs', 0);
        // Avoid integer multiplication overflow even with a corrupt/oversized legacy stack.
        DB::table('backpacks')->update(['count' => PHP_INT_MAX]);
        $this->assertFalse(app(SellItems::class)->execute($this->economyUser(), $this->sellSelection(PHP_INT_MAX))->ok);
    }

    public function test_inventory_decrement_guard_rejects_insufficient_or_newly_equipped_items(): void
    {
        $item = Backpack::query()->findOrFail(1);
        $inventory = app(ShopInventoryRepository::class);
        $this->assertFalse($inventory->consumeBackpackItem($item, 11));
        $this->assertFalse($inventory->consumeBackpackItem($item, -1));
        DB::table('backpacks')->update(['equipped' => 1]);
        $this->assertFalse($inventory->consumeBackpackItem($item, 1));
        $this->assertDatabaseHas('backpacks', ['id' => 1, 'count' => 10]);
    }

    public function test_zero_price_items_can_still_be_removed(): void
    {
        DB::table('share_items')->update(['price' => 0]);
        $this->assertTrue(app(SellItems::class)->execute($this->economyUser(), $this->sellSelection())->ok);
        $this->assertDatabaseCount('backpacks', 0);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('item_action_logs', ['money' => 0, 'count' => 10]);
    }

    public function test_failed_sale_log_restores_money_items_and_counts(): void
    {
        $logger = Mockery::mock(ItemActionLogger::class);
        $logger->shouldReceive('log')->once()->andThrow(new RuntimeException('Audit failed'));
        $this->app->instance(ItemActionLogger::class, $logger);
        $this->expectException(RuntimeException::class);
        try {
            app(SellItems::class)->execute($this->economyUser(), $this->sellSelection());
        } finally {
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
            $this->assertDatabaseHas('backpacks', ['count' => 10]);
            $this->assertDatabaseCount('items', 1);
        }
    }

    public function test_selling_a_shared_legacy_item_does_not_delete_another_players_inventory(): void
    {
        DB::table('backpacks')->insert(['user_id' => 2, 'item_id' => 1, 'count' => 4]);
        $this->assertTrue(app(SellItems::class)->execute($this->economyUser(), $this->sellSelection())->ok);
        $this->assertDatabaseHas('backpacks', ['user_id' => 2, 'count' => 4]);
        $this->assertDatabaseCount('items', 1);
    }

    public function test_normal_and_premium_purchases_reuse_the_current_stack_after_partial_sale(): void
    {
        foreach ([false, true] as $premium) {
            $this->seedEconomyCart(premium: $premium);
            app(SellItems::class)->execute($this->economyUser(), $this->sellSelection(2));
            $action = $premium ? \App\Modules\Structure\PremiumShop\Application\UseCases\PurchaseCart::class : PurchaseCart::class;
            $this->assertTrue(app($action)->execute($this->economyUser(), $premium ? 3 : 2)->ok);
            $this->assertDatabaseCount('backpacks', 1);
            $this->assertDatabaseHas('backpacks', ['count' => 10]);
        }
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'diamond' => 60]);
        $this->assertDatabaseCount('shop_purchase_logs', 2);
        $this->assertDatabaseCount('shop_carts', 0);
    }
}
