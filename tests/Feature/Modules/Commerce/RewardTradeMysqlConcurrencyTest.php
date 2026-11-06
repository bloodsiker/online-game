<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use App\Modules\PlayerTrade\Application\Services\PlayerTradeService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesRewardTradeFixture;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class RewardTradeMysqlConcurrencyTest extends TestCase
{
    use CreatesRewardTradeFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('REWARD_TRADE_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires an explicitly created codex_reward_trade_race_* MySQL database.');
        }
        $this->assertMatchesRegularExpression('/^codex_reward_trade_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        Carbon::setTestNow('2026-11-03 12:01:00');
        if (! Schema::hasTable('users')) {
            $this->createRewardTradeTables();
        }
        // Never enumerate schemas here: getTableListing() without an explicit schema
        // includes other accessible databases and returns qualified names in Laravel 11.
        // Pin the checked connection and delete only these unqualified fixture tables.
        $connection = DB::connection();
        $this->assertSame($this->database, $connection->getDatabaseName());
        Schema::withoutForeignKeyConstraints(function () use ($connection): void {
            foreach ([
                'referral_reward_claims', 'referral_reward_stages', 'referrals', 'player_trade_sessions', 'chat_messages',
                'shop_purchase_logs', 'item_action_logs', 'clan_tax_payments', 'clan_treasury_logs', 'clan_warehouse_logs',
                'clan_warehouses', 'clan_logs', 'clan_join_requests', 'clan_members', 'clan_roles', 'clans',
                'shop_carts', 'shop_item_requirements', 'shop_items', 'item_on_locations', 'item_gems', 'item_runes',
                'backpacks', 'items', 'share_item_instant_rewards', 'share_recipes', 'share_items',
                'structures', 'locations', 'users', 'players',
            ] as $table) {
                $connection->table($table)->delete();
            }
        });
        $this->seedRewardTradeFixture();
        $this->bindRewardTradeDoubles();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function race(array $first, ?array $second = null, ?callable $holdLocks = null): array
    {
        return $this->runConcurrentRequests('tests/Support/reward-trade-race-worker.php', $this->database,
            'REWARD_TRADE_RACE_TEST_DATABASE', $first, $second, $holdLocks);
    }

    private function lockTrades(): void
    {
        DB::table('player_trade_sessions')->orderBy('id')->lockForUpdate()->get();
    }

    public function test_duplicate_level_events_deliver_each_stage_once(): void
    {
        $this->assertSame([['ok' => true], ['ok' => true]], $this->race(['reward', 2, '']));
        $this->assertDatabaseCount('referral_reward_claims', 3);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 107]);
        $this->assertSame(13, (int) DB::table('backpacks')->sum('count'));
    }

    public function test_two_referrals_credit_the_same_referrer_without_losing_rewards(): void
    {
        DB::table('referrals')->insert(['id' => 2, 'referrer_user_id' => 1, 'referred_user_id' => 3]);
        $this->assertSame([['ok' => true], ['ok' => true]], $this->race(['reward', 2, ''], ['reward', 3, '']));
        $this->assertDatabaseCount('referral_reward_claims', 6);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 200, 'diamond' => 114]);
        $this->assertSame(16, (int) DB::table('backpacks')->sum('count'));
    }

    public function test_mutual_referrals_use_the_same_lock_order(): void
    {
        DB::table('referrals')->insert(['id' => 2, 'referrer_user_id' => 2, 'referred_user_id' => 1]);
        $this->assertSame([['ok' => true], ['ok' => true]], $this->race(['reward', 2, ''], ['reward', 1, '']));
        $this->assertDatabaseCount('referral_reward_claims', 6);
        foreach ([1, 2] as $id) {
            $this->assertDatabaseHas('users', ['id' => $id, 'money' => 150, 'diamond' => 107]);
        }
    }

    public function test_referral_reward_and_trade_preserve_both_money_changes(): void
    {
        $trade = $this->seedRewardTradeSession();
        $this->assertSame([['ok' => true], ['ok' => true]], $this->race(['reward', 2, ''], ['confirm', 2, $trade->uuid]));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 135, 'diamond' => 107]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 115]);
        $this->assertSame(13, (int) DB::table('backpacks')->sum('count'));
        $this->assertSame('completed', $trade->fresh()->status);
    }

    public function test_double_final_confirmation_transfers_only_once(): void
    {
        $trade = $this->seedRewardTradeSession();
        $results = $this->race(['confirm', 2, $trade->uuid], holdLocks: $this->lockTrades(...));
        $this->assertCount(1, array_filter($results, fn ($result) => $result['ok']));
        $this->assertSame('completed', $trade->fresh()->status);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 85]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 115]);
        $this->assertSame(2, (int) DB::table('backpacks')->where('user_id', 2)->sum('count'));
    }

    public function test_cancel_and_confirm_cannot_partially_settle_a_trade(): void
    {
        $trade = $this->seedRewardTradeSession();
        $this->race(['confirm', 2, $trade->uuid], ['cancel', 1, $trade->uuid], $this->lockTrades(...));
        $completed = $trade->fresh()->status === 'completed';
        $this->assertContains($trade->fresh()->status, ['completed', 'cancelled']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => $completed ? 85 : 100]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => $completed ? 115 : 100]);
        $this->assertSame($completed ? 2 : 0, (int) DB::table('backpacks')->where('user_id', 2)->sum('count'));
    }

    public function test_expired_page_waits_for_completion_instead_of_overwriting_it(): void
    {
        $trade = $this->seedRewardTradeSession();
        DB::table('player_trade_sessions')->where('id', $trade->id)->update(['expires_at' => '2026-11-03 12:00:30']);
        $this->race(['page', 1, $trade->uuid], ['page', 2, $trade->uuid], function () use ($trade): void {
            Carbon::setTestNow('2026-11-03 12:00:00');
            app(PlayerTradeService::class)->confirm(User::findOrFail(2), $trade->uuid);
            Carbon::setTestNow('2026-11-03 12:01:00');
        });
        $this->assertSame('completed', $trade->fresh()->status);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 85]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 115]);
    }

    public function test_offer_edit_and_final_confirmation_use_one_consistent_offer(): void
    {
        $trade = $this->seedRewardTradeSession();
        $this->race(['confirm', 2, $trade->uuid], ['offer', 1, $trade->uuid], $this->lockTrades(...));
        $trade->refresh();
        $completed = $trade->status === 'completed';
        $this->assertContains($trade->status, ['active', 'completed']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => $completed ? 85 : 100]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => $completed ? 115 : 100]);
        $this->assertSame($completed ? 20 : 30, $trade->initiator_offer['money']);
        $this->assertSame($completed, $trade->initiator_confirmed);
        $this->assertTrue($trade->target_confirmed);
        $this->assertSame($completed ? 2 : 0, (int) DB::table('backpacks')->where('user_id', 2)->sum('count'));
    }
}
