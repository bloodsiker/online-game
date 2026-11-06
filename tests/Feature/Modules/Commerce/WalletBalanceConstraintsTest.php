<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use App\Modules\User\Domain\Services\WalletBalanceLimits;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\CreatesWalletConstraintFixture;
use Tests\TestCase;

class WalletBalanceConstraintsTest extends TestCase
{
    use CreatesWalletConstraintFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareWalletConstraintFixture();
    }

    public static function invalidBalances(): iterable
    {
        foreach (['money', 'diamond'] as $column) {
            foreach (['negative' => -1, 'overflow' => 2147483648, 'null' => null] as $label => $value) {
                yield $column.' '.$label => [$column, $value];
            }
        }
    }

    #[DataProvider('invalidBalances')]
    public function test_direct_updates_cannot_bypass_wallet_range(string $column, ?int $value): void
    {
        $this->walletMigration()->up();
        $this->expectException(QueryException::class);
        DB::table('users')->where('id', 1)->update([$column => $value]);
    }

    #[DataProvider('invalidBalances')]
    public function test_direct_inserts_cannot_bypass_wallet_range(string $column, ?int $value): void
    {
        $this->walletMigration()->up();
        $this->expectException(QueryException::class);
        DB::table('users')->insert(['name' => 'Invalid', $column => $value]);
    }

    #[DataProvider('invalidBalances')]
    public function test_preflight_preserves_invalid_data_and_adds_no_partial_constraints(string $column, ?int $value): void
    {
        DB::table('users')->where('id', 1)->update([$column => $value]);
        try {
            $this->walletMigration()->up();
            $this->fail('Invalid balances require an explicit decision, not automatic correction.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('invalid_count=1', $exception->getMessage());
            $this->assertStringContainsString($column, $exception->getMessage());
            $this->assertStringContainsString('No rows were changed', $exception->getMessage());
        }
        $this->assertDatabaseHas('users', ['id' => 1, $column => $value]);
        // Both invalid inserts must still work: neither constraint was partially installed.
        DB::table('users')->insert(['name' => 'Preflight untouched', 'money' => -1, 'diamond' => -1]);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_zero_maximum_defaults_and_atomic_wallet_operations_are_allowed(): void
    {
        $this->walletMigration()->up();
        DB::table('users')->where('id', 1)->update(['money' => 0, 'diamond' => WalletBalanceLimits::MAX_BALANCE]);
        DB::table('users')->where('id', 1)->increment('money', WalletBalanceLimits::MAX_BALANCE);
        DB::table('users')->where('id', 1)->decrement('diamond', WalletBalanceLimits::MAX_BALANCE);
        DB::table('users')->insert(['name' => 'Defaults']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => WalletBalanceLimits::MAX_BALANCE, 'diamond' => 0]);
        $this->assertDatabaseHas('users', ['name' => 'Defaults', 'money' => 0, 'diamond' => 0]);
    }

    public function test_constraint_failure_rolls_back_other_money_changes_and_receipts(): void
    {
        $this->walletMigration()->up();
        try {
            DB::transaction(function (): void {
                DB::table('wallet_test_receipts')->insert(['user_id' => 1, 'amount' => 10]);
                DB::table('users')->where('id', 1)->increment('diamond', 5);
                DB::table('users')->where('id', 1)->decrement('money', 11);
            });
            $this->fail('The overdraft must fail.');
        } catch (QueryException) {
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 10, 'diamond' => 10]);
            $this->assertDatabaseCount('wallet_test_receipts', 0);
        }
    }

    public function test_reapplying_and_rollback_preserve_data_indexes_and_foreign_keys(): void
    {
        $this->walletMigration()->up();
        $this->walletMigration()->up();
        DB::table('wallet_test_receipts')->insert(['user_id' => 1]);
        $this->walletMigration()->down();
        $this->walletMigration()->down();
        $this->assertDatabaseHas('users', ['id' => 1, 'name' => 'Wallet fixture', 'money' => 10]);
        $this->assertDatabaseCount('wallet_test_receipts', 1);
        $this->assertTrue(Schema::hasIndex('users', 'users_name_unique'));
        $this->assertNotEmpty(Schema::getForeignKeys('wallet_test_receipts'));
        DB::table('users')->where('id', 1)->update(['money' => -1, 'diamond' => -1]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => -1, 'diamond' => -1]);
        $this->expectException(QueryException::class);
        DB::table('wallet_test_receipts')->insert(['user_id' => 999]);
    }

    public function test_one_bulk_update_cannot_partially_change_users(): void
    {
        $this->walletMigration()->up();
        DB::table('users')->insert(['id' => 2, 'name' => 'Poor', 'money' => 1]);
        try {
            DB::table('users')->decrement('money', 2);
            $this->fail('The bulk overdraft must fail.');
        } catch (QueryException) {
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 10]);
            $this->assertDatabaseHas('users', ['id' => 2, 'money' => 1]);
        }
    }

    public function test_mysql_reenforces_an_existing_disabled_constraint(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('MySQL-specific CHECK enforcement.');
        }
        $this->walletMigration()->up();
        DB::statement('ALTER TABLE users ALTER CHECK users_money_wallet_range NOT ENFORCED');
        $this->walletMigration()->up();
        $this->expectException(QueryException::class);
        DB::table('users')->where('id', 1)->update(['money' => -1]);
    }
}
