<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesWalletConstraintFixture;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class WalletMysqlConcurrencyTest extends TestCase
{
    use CreatesWalletConstraintFixture;
    use RunsConcurrentRequests;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('WALLET_CONSTRAINT_TEST_DATABASE')) {
            $this->markTestSkipped('Requires an explicitly created codex_wallet_constraint_* MySQL database.');
        }
        $this->prepareWalletConstraintFixture();
        $this->walletMigration()->up();
    }

    public static function conflictingBalances(): iterable
    {
        foreach (['money', 'diamond'] as $column) {
            yield $column.' overdraft' => [$column, 10, -7, 3];
            yield $column.' overflow' => [$column, 2147483646, 1, 2147483647];
        }
    }

    #[DataProvider('conflictingBalances')]
    public function test_database_rejects_the_losing_request_and_rolls_back_its_other_changes(string $column, int $initial, int $delta, int $expected): void
    {
        DB::table('users')->where('id', 1)->update([$column => $initial]);
        $database = (string) getenv('WALLET_CONSTRAINT_TEST_DATABASE');
        $results = $this->runConcurrentRequests(worker: 'tests/Support/wallet-constraint-race-worker.php', database: $database,
            databaseVariable: 'WALLET_CONSTRAINT_TEST_DATABASE', first: [$column, $delta]);
        $this->assertCount(1, array_filter($results, fn ($result) => $result['ok']));
        $other = $column === 'money' ? 'diamond' : 'money';
        $this->assertDatabaseHas('users', ['id' => 1, $column => $expected, $other => 11]);
        $this->assertDatabaseCount('wallet_test_receipts', 1);
        $this->assertDatabaseHas('wallet_test_receipts', ['user_id' => 1, 'amount' => $delta]);
    }
}
