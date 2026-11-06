<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure\Bank;

use App\Modules\Structure\Bank\Application\UseCases\ClaimDeposit;
use App\Modules\Structure\Bank\Application\UseCases\Deposit;
use App\Modules\Structure\Bank\Application\UseCases\OpenDeposit;
use App\Modules\Structure\Bank\Application\UseCases\Transfer;
use App\Modules\Structure\Bank\Application\UseCases\Withdraw;
use App\Modules\Structure\Bank\Domain\Contracts\BankDepositRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankLogRepository;
use App\Modules\Structure\Bank\Domain\Contracts\BankUserRepository;
use App\Modules\Structure\Bank\Domain\Models\BankDeposit;
use App\Modules\Structure\Bank\Domain\Services\BankBalanceLimits;
use App\Modules\Structure\Bank\Infrastructure\Persistence\EloquentBankUserRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class BankTransactionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('money')->default(0);
            $table->unsignedBigInteger('bank_balance')->default(0);
            $table->string('bank_account')->nullable()->unique();
            $table->timestamps();
        });
        (require database_path('migrations/2026_07_07_000001_create_bank_deposits_table.php'))->up();
        (require database_path('migrations/2026_03_25_100004_create_bank_logs_table.php'))->up();
        DB::table('users')->insert([
            ['id' => 1, 'money' => 100, 'bank_balance' => 50, 'bank_account' => '0001'],
            ['id' => 2, 'money' => 0, 'bank_balance' => 0, 'bank_account' => '0002'],
        ]);
        $this->freezeTime();
    }

    private function user(int $id = 1): User
    {
        return User::without('player')->findOrFail($id);
    }

    private function termDeposit(bool $matured = true): BankDeposit
    {
        return BankDeposit::create([
            'user_id' => 1, 'amount' => 10000, 'percent' => 0.04, 'term_days' => 14,
            'matures_at' => $matured ? now()->subSecond() : now()->addDays(14),
        ]);
    }

    public function test_deposit_rechecks_fresh_balance_for_repeated_requests_with_a_stale_user(): void
    {
        $stale = $this->user();
        $this->assertTrue(app(Deposit::class)->execute($stale, 80)->ok);
        $this->assertFalse(app(Deposit::class)->execute($stale, 80)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 20, 'bank_balance' => 130]);
        $this->assertDatabaseCount('bank_logs', 1);
        $this->assertDatabaseHas('bank_logs', ['action' => 'deposit', 'amount' => 80, 'balance_after' => 130]);
    }

    public function test_withdraw_rechecks_fresh_balance_and_does_not_pay_twice(): void
    {
        $stale = $this->user();
        $this->assertTrue(app(Withdraw::class)->execute($stale, 40)->ok);
        $this->assertFalse(app(Withdraw::class)->execute($stale, 40)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 140, 'bank_balance' => 10]);
        $this->assertDatabaseCount('bank_logs', 1);
        $this->assertDatabaseHas('bank_logs', ['action' => 'withdraw', 'amount' => 40, 'balance_after' => 10]);
    }

    public function test_balance_checks_use_database_state_even_when_the_passed_user_claims_to_have_more_money(): void
    {
        $stale = $this->user();
        DB::table('users')->where('id', 1)->update(['money' => 1, 'bank_balance' => 1]);
        $this->assertFalse(app(Deposit::class)->execute($stale, 50)->ok);
        $this->assertFalse(app(Withdraw::class)->execute($stale, 20)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 1, 'bank_balance' => 1]);
        $this->assertDatabaseCount('bank_logs', 0);
    }

    public function test_claim_pays_principal_and_interest_exactly_once(): void
    {
        $deposit = $this->termDeposit();
        $stale = $this->user();
        $this->assertTrue(app(ClaimDeposit::class)->execute($stale, $deposit->id)->ok);
        $this->assertFalse(app(ClaimDeposit::class)->execute($stale, $deposit->id)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 10156]);
        $this->assertNotNull($deposit->fresh()->closed_at);
    }

    public function test_early_claim_returns_only_principal_and_rejects_other_owners(): void
    {
        $deposit = $this->termDeposit(matured: false);
        $this->assertFalse(app(ClaimDeposit::class)->execute($this->user(2), $deposit->id)->ok);
        $this->assertNull($deposit->fresh()->closed_at);
        $this->assertTrue(app(ClaimDeposit::class)->execute($this->user(), $deposit->id)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 10100]);
    }

    public function test_conditional_close_cannot_claim_an_already_closed_deposit_using_a_stale_model(): void
    {
        $deposit = $this->termDeposit();
        $repository = app(BankDepositRepository::class);
        $this->assertTrue($repository->close($deposit));
        $this->assertFalse($repository->close($deposit));
    }

    public function test_open_deposit_allows_only_one_open_deposit_per_term_under_repeated_requests(): void
    {
        DB::table('users')->where('id', 1)->update(['money' => 30000]);
        $stale = $this->user();
        $this->assertTrue(app(OpenDeposit::class)->execute($stale, 10000, 14)->ok);
        $this->assertFalse(app(OpenDeposit::class)->execute($stale, 10000, 14)->ok);
        $this->assertDatabaseCount('bank_deposits', 1);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 20000]);
        $this->assertTrue(app(OpenDeposit::class)->execute($stale, 10000, 30)->ok);
        $this->assertDatabaseCount('bank_deposits', 2);
    }

    public function test_open_deposit_cannot_spend_a_stale_balance_or_an_invalid_amount_or_term(): void
    {
        $stale = $this->user()->forceFill(['money' => 100000]);
        $this->assertFalse(app(OpenDeposit::class)->execute($stale, 10000, 14)->ok);
        $this->assertFalse(app(OpenDeposit::class)->execute($stale, 0, 14)->ok);
        $this->assertFalse(app(OpenDeposit::class)->execute($stale, 10000, 15)->ok);
        $this->assertDatabaseCount('bank_deposits', 0);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
    }

    public function test_transfer_rechecks_balances_and_records_both_accounts_only_once(): void
    {
        DB::table('users')->where('id', 1)->update(['bank_balance' => 100]);
        $stale = $this->user();
        $this->assertTrue(app(Transfer::class)->execute($stale, '0002', 90)->ok);
        $this->assertFalse(app(Transfer::class)->execute($stale, '0002', 90)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'bank_balance' => 9]);
        $this->assertDatabaseHas('users', ['id' => 2, 'bank_balance' => 90]);
        $this->assertDatabaseCount('bank_logs', 2);
        $this->assertDatabaseHas('bank_logs', ['user_id' => 1, 'action' => 'transfer_out', 'amount' => 91, 'balance_after' => 9]);
        $this->assertDatabaseHas('bank_logs', ['user_id' => 2, 'action' => 'transfer_in', 'amount' => 90, 'balance_after' => 90]);
    }

    public function test_transfer_uses_exact_rounded_up_commission_and_rejects_invalid_recipients(): void
    {
        DB::table('users')->where('id', 1)->update(['bank_balance' => 1000]);
        $this->assertFalse(app(Transfer::class)->execute($this->user(), '0001', 100)->ok);
        $this->assertFalse(app(Transfer::class)->execute($this->user(), 'missing', 100)->ok);
        $this->assertTrue(app(Transfer::class)->execute($this->user(), '0002', 101)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'bank_balance' => 897]);
        $this->assertDatabaseHas('users', ['id' => 2, 'bank_balance' => 101]);
    }

    public function test_zero_and_negative_amounts_cannot_reverse_money_operations(): void
    {
        foreach ([0, -10] as $amount) {
            $this->assertFalse(app(Deposit::class)->execute($this->user(), $amount)->ok);
            $this->assertFalse(app(Withdraw::class)->execute($this->user(), $amount)->ok);
            $this->assertFalse(app(Transfer::class)->execute($this->user(), '0002', $amount)->ok);
        }
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'bank_balance' => 50]);
        $this->assertDatabaseCount('bank_logs', 0);
    }

    public function test_guarded_decrements_refuse_overdrafts_even_when_called_directly(): void
    {
        $repository = app(BankUserRepository::class);
        $this->assertFalse($repository->decrementMoney($this->user(), 101));
        $this->assertFalse($repository->decrementBankBalance($this->user(), 51));
        $this->assertFalse($repository->decrementMoney($this->user(), -1));
        $this->assertFalse($repository->decrementBankBalance($this->user(), -1));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'bank_balance' => 50]);
    }

    public function test_failed_audit_write_rolls_back_both_balances(): void
    {
        $logs = Mockery::mock(BankLogRepository::class);
        $logs->shouldReceive('create')->once()->andThrow(new RuntimeException('audit unavailable'));
        $this->app->instance(BankLogRepository::class, $logs);
        try {
            app(Deposit::class)->execute($this->user(), 80);
            $this->fail('The failed operation must roll back.');
        } catch (RuntimeException $exception) {
            $this->assertSame('audit unavailable', $exception->getMessage());
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'bank_balance' => 50]);
        }
    }

    public function test_failed_payout_keeps_deposit_open_for_retry(): void
    {
        $deposit = $this->termDeposit();
        $repository = Mockery::mock(EloquentBankUserRepository::class)->makePartial();
        $repository->shouldReceive('incrementMoney')->once()->andThrow(new RuntimeException('payment unavailable'));
        $this->app->instance(BankUserRepository::class, $repository);
        try {
            app(ClaimDeposit::class)->execute($this->user(), $deposit->id);
            $this->fail('The failed payout must roll back.');
        } catch (RuntimeException $exception) {
            $this->assertSame('payment unavailable', $exception->getMessage());
            $this->assertNull($deposit->fresh()->closed_at);
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        }
    }

    public function test_wallet_overflow_does_not_spend_bank_balance_or_close_deposit(): void
    {
        DB::table('users')->where('id', 1)->update(['money' => BankBalanceLimits::MAX_WALLET, 'bank_balance' => 100]);
        $deposit = $this->termDeposit();
        $this->assertFalse(app(Withdraw::class)->execute($this->user(), 1)->ok);
        $this->assertFalse(app(ClaimDeposit::class)->execute($this->user(), $deposit->id)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => BankBalanceLimits::MAX_WALLET, 'bank_balance' => 100]);
        $this->assertNull($deposit->fresh()->closed_at);
    }
}
