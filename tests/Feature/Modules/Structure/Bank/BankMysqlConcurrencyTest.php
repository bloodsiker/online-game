<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure\Bank;

use App\Modules\Structure\Bank\Domain\Models\BankDeposit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class BankMysqlConcurrencyTest extends TestCase
{
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('BANK_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires an explicitly created isolated MySQL database named codex_bank_race_*.');
        }
        // Fail before any fixture mutation if env/config accidentally points at the game database.
        $this->assertMatchesRegularExpression('/^codex_bank_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->integer('money')->default(0);
                $table->unsignedBigInteger('bank_balance')->default(0);
                $table->string('bank_account')->unique();
                $table->timestamps();
            });
            (require database_path('migrations/2026_07_07_000001_create_bank_deposits_table.php'))->up();
            (require database_path('migrations/2026_03_25_100004_create_bank_logs_table.php'))->up();
        }
        DB::table('bank_logs')->delete();
        DB::table('bank_deposits')->delete();
        DB::table('users')->delete();
        DB::table('users')->insert([
            ['id' => 1, 'money' => 100, 'bank_balance' => 100, 'bank_account' => '0001'],
            ['id' => 2, 'money' => 0, 'bank_balance' => 100, 'bank_account' => '0002'],
        ]);
    }

    private function race(array $first, ?array $second = null): array
    {
        return $this->runConcurrentRequests('tests/Support/bank-race-worker.php', $this->database, 'BANK_RACE_TEST_DATABASE', $first, $second);
    }

    public function test_parallel_deposits_cannot_overdraw_the_wallet(): void
    {
        $results = $this->race(['deposit', 1, 80, '']);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 20, 'bank_balance' => 180]);
        $this->assertDatabaseCount('bank_logs', 1);
    }

    public function test_parallel_withdrawals_cannot_overdraw_the_account(): void
    {
        $results = $this->race(['withdraw', 1, 80, '']);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 180, 'bank_balance' => 20]);
        $this->assertDatabaseCount('bank_logs', 1);
    }

    public function test_parallel_claims_pay_a_matured_deposit_once(): void
    {
        $deposit = BankDeposit::create(['user_id' => 1, 'amount' => 10000, 'percent' => 0.04, 'term_days' => 14, 'matures_at' => now()->subDay()]);
        $results = $this->race(['claim', 1, 0, $deposit->id]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 10156]);
        $this->assertNotNull($deposit->fresh()->closed_at);
    }

    public function test_parallel_open_requests_create_only_one_deposit_for_the_same_term(): void
    {
        DB::table('users')->where('id', 1)->update(['money' => 30000]);
        $results = $this->race(['open', 1, 10000, 14]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 20000]);
        $this->assertDatabaseCount('bank_deposits', 1);
    }

    public function test_parallel_transfers_cannot_overdraw_the_sender(): void
    {
        $results = $this->race(['transfer', 1, 90, '0002']);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'bank_balance' => 9]);
        $this->assertDatabaseHas('users', ['id' => 2, 'bank_balance' => 190]);
        $this->assertDatabaseCount('bank_logs', 2);
    }

    public function test_opposite_transfers_lock_accounts_in_the_same_order_without_deadlock(): void
    {
        DB::table('users')->update(['bank_balance' => 1000]);
        $results = $this->race(['transfer', 1, 100, '0002'], ['transfer', 2, 100, '0001']);
        $this->assertSame(2, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'bank_balance' => 999]);
        $this->assertDatabaseHas('users', ['id' => 2, 'bank_balance' => 999]);
        $this->assertDatabaseCount('bank_logs', 4);
    }
}
