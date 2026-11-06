<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Clan\Application\UseCases\DepositClanTreasury;
use App\Modules\Clan\Application\UseCases\WithdrawClanTreasury;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\Support\CreatesEconomyFixture;
use Tests\TestCase;

class TreasuryTransactionsTest extends TestCase
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

    public function test_repeated_deposits_recheck_current_wallet_despite_stale_user(): void
    {
        $user = $this->economyUser();
        app(DepositClanTreasury::class)->execute($user, 1, 80);
        try {
            app(DepositClanTreasury::class)->execute($user, 1, 80);
            $this->fail('Second deposit should be rejected.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('кошельке', $e->getMessage());
        }
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 20]);
        $this->assertDatabaseHas('clans', ['id' => 1, 'treasury' => 180]);
        $this->assertDatabaseHas('clan_treasury_logs', ['amount' => 80, 'balance_after' => 180]);
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_repeated_withdrawals_recheck_the_treasury_despite_cached_membership(): void
    {
        $user = $this->economyUser();
        app(WithdrawClanTreasury::class)->execute($user, 1, 80);
        $this->expectException(RuntimeException::class);
        try {
            app(WithdrawClanTreasury::class)->execute($user, 1, 80);
        } finally {
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 180]);
            $this->assertDatabaseHas('clans', ['id' => 1, 'treasury' => 20]);
            $this->assertDatabaseCount('clan_treasury_logs', 1);
        }
    }

    public function test_permission_revocation_is_rechecked_after_loading_the_users_context(): void
    {
        $user = $this->economyUser(2);
        $user->load('clanMembership.clan', 'clanMembership.role');
        DB::table('clan_roles')->where('id', 2)->update(['permissions' => 0]);
        $this->expectException(RuntimeException::class);
        try {
            app(WithdrawClanTreasury::class)->execute($user, 1, 10);
        } finally {
            $this->assertDatabaseHas('clans', ['treasury' => 100]);
            $this->assertDatabaseCount('clan_treasury_logs', 0);
        }
    }

    public function test_removed_member_cannot_use_a_cached_clan_context(): void
    {
        $user = $this->economyUser(2);
        $user->load('clanMembership.clan', 'clanMembership.role');
        DB::table('clan_members')->where('user_id', 2)->delete();
        $this->expectException(RuntimeException::class);
        try {
            app(DepositClanTreasury::class)->execute($user, 1, 10);
        } finally {
            $this->assertDatabaseHas('users', ['id' => 2, 'money' => 100]);
            $this->assertDatabaseCount('clan_treasury_logs', 0);
        }
    }

    public function test_unpaid_tax_blocks_regular_members_but_preserves_leader_access(): void
    {
        DB::table('clans')->update(['tax_paid_until' => now()->subDay()]);
        foreach ([DepositClanTreasury::class, WithdrawClanTreasury::class] as $action) {
            try {
                app($action)->execute($this->economyUser(2), 1, 10);
                $this->fail('Tax restriction was bypassed.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('налога', $e->getMessage());
            }
            app($action)->execute($this->economyUser(), 1, 10);
        }
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('clans', ['id' => 1, 'treasury' => 100]);
        $this->assertDatabaseCount('clan_treasury_logs', 2);
    }

    public function test_invalid_amounts_and_wallet_overflow_leave_both_balances_unchanged(): void
    {
        foreach ([DepositClanTreasury::class, WithdrawClanTreasury::class] as $action) {
            foreach ([0, -1, PHP_INT_MAX] as $amount) {
                try {
                    app($action)->execute($this->economyUser(), 1, $amount);
                    $this->fail('Invalid amount accepted.');
                } catch (RuntimeException) {
                    $this->assertDatabaseHas('clans', ['treasury' => 100]);
                }
            }
        }
        DB::table('users')->where('id', 1)->update(['money' => WalletBalanceLimits::MAX_BALANCE]);
        $this->expectException(RuntimeException::class);
        try {
            app(WithdrawClanTreasury::class)->execute($this->economyUser(), 1, 1);
        } finally {
            $this->assertDatabaseHas('clans', ['treasury' => 100]);
            $this->assertDatabaseCount('clan_treasury_logs', 0);
        }
    }

    public function test_treasury_integer_limit_rejects_deposit_before_charging_the_player(): void
    {
        DB::table('clans')->update(['treasury' => PHP_INT_MAX]);
        $this->expectException(RuntimeException::class);
        try {
            app(DepositClanTreasury::class)->execute($this->economyUser(), 1, 1);
        } finally {
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
            $this->assertDatabaseCount('clan_treasury_logs', 0);
        }
    }

    public function test_failed_audit_write_rolls_back_deposit_and_withdrawal(): void
    {
        $logger = Mockery::mock(ClanLogService::class);
        $logger->shouldReceive('write')->twice()->andThrow(new RuntimeException('Audit failed'));
        $this->app->instance(ClanLogService::class, $logger);
        foreach ([DepositClanTreasury::class, WithdrawClanTreasury::class] as $action) {
            try {
                app($action)->execute($this->economyUser(), 1, 10);
                $this->fail('Audit failure should propagate.');
            } catch (RuntimeException $e) {
                $this->assertSame('Audit failed', $e->getMessage());
            }
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
            $this->assertDatabaseHas('clans', ['id' => 1, 'treasury' => 100]);
            $this->assertDatabaseCount('clan_treasury_logs', 0);
        }
    }
}
