<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\PlayerTrade\Application\Services\PlayerTradeService;
use App\Modules\PlayerTrade\Domain\Events\PlayerTradeChanged;
use App\Modules\Referral\Application\UseCases\GrantReferralRewards;
use App\Modules\Referral\Domain\Contracts\ReferralRewardIssuer;
use App\Modules\Referral\Infrastructure\Persistence\EloquentReferralRewardIssuer;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use RuntimeException;
use Tests\Support\CreatesRewardTradeFixture;
use Tests\TestCase;

class RewardTradeTransactionsTest extends TestCase
{
    use CreatesRewardTradeFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createRewardTradeTables();
        $this->seedRewardTradeFixture();
        $this->bindRewardTradeDoubles();
    }

    private function grant(): void
    {
        app(GrantReferralRewards::class)->handle(Player::findOrFail(2));
    }

    private function rejected(callable $action): void
    {
        try {
            $action();
            $this->fail('The invalid operation must not commit.');
        } catch (RuntimeException $exception) {
            $this->assertNotSame('', $exception->getMessage());
        }
    }

    public function test_referral_stages_and_all_reward_types_are_granted_once(): void
    {
        $this->grant();
        $this->grant();
        $this->assertDatabaseCount('referral_reward_claims', 3);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 107]);
        $this->assertSame(13, (int) DB::table('backpacks')->where('user_id', 1)->sum('count'));
    }

    public function test_actual_level_is_used_instead_of_the_event_snapshot(): void
    {
        $stale = Player::findOrFail(2);
        $stale->lvl = 100;
        DB::table('players')->where('id', 2)->update(['lvl' => 5]);
        app(GrantReferralRewards::class)->handle($stale);
        $this->assertDatabaseCount('referral_reward_claims', 1);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 100]);
        DB::table('players')->where('id', 2)->update(['lvl' => 10]);
        $stale->lvl = 5;
        app(GrantReferralRewards::class)->handle($stale);
        $this->assertDatabaseCount('referral_reward_claims', 3);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 107]);
    }

    public function test_failed_item_delivery_rolls_back_claims_and_all_previous_rewards(): void
    {
        $actual = new EloquentReferralRewardIssuer;
        $issuer = Mockery::mock(ReferralRewardIssuer::class);
        $issuer->shouldReceive('grantGold')->andReturnUsing($actual->grantGold(...));
        $issuer->shouldReceive('grantDiamonds')->andReturnUsing($actual->grantDiamonds(...));
        $issuer->shouldReceive('grantItem')->andReturnUsing(function (int $userId, int $itemId, int $count) use ($actual): void {
            $actual->grantItem($userId, $itemId, $count);
            throw new RuntimeException('Item delivery failed');
        });
        app()->instance(ReferralRewardIssuer::class, $issuer);
        $this->rejected(fn () => $this->grant());
        $this->assertDatabaseCount('referral_reward_claims', 0);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'diamond' => 100]);
        $this->assertDatabaseCount('items', 1);
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count'));
    }

    public function test_wallet_overflow_does_not_mark_referral_rewards_claimed(): void
    {
        DB::table('users')->where('id', 1)->update(['diamond' => WalletBalanceLimits::MAX_BALANCE]);
        $this->rejected(fn () => $this->grant());
        $this->assertDatabaseCount('referral_reward_claims', 0);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'diamond' => WalletBalanceLimits::MAX_BALANCE]);
    }

    public function test_invalid_item_config_is_not_silently_marked_claimed(): void
    {
        DB::table('referral_reward_stages')->where('id', 3)->update(['reward_item_id' => null]);
        $this->rejected(fn () => $this->grant());
        $this->assertDatabaseCount('referral_reward_claims', 0);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
    }

    public function test_referral_awards_are_restored_by_outer_rollback_and_can_be_retried(): void
    {
        DB::beginTransaction();
        $this->grant();
        DB::rollBack();
        $this->assertDatabaseCount('referral_reward_claims', 0);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'diamond' => 100]);
        $this->grant();
        $this->assertDatabaseCount('referral_reward_claims', 3);
    }

    public function test_expired_page_and_state_cannot_cancel_a_completed_trade(): void
    {
        $trade = $this->seedRewardTradeSession();
        $service = app(PlayerTradeService::class);
        $service->confirm($this->economyUser(2), $trade->uuid);
        DB::table('player_trade_sessions')->where('id', $trade->id)->update(['expires_at' => now()->subMinute()]);
        $before = DB::table('player_trade_sessions')->where('id', $trade->id)->first();
        Event::fake([PlayerTradeChanged::class]);
        $this->assertSame('completed', $service->page($this->economyUser(), $trade->uuid)['trade']->status);
        $this->assertSame('completed', $service->state($this->economyUser(2), $trade->uuid)['status']);
        $this->assertEquals($before, DB::table('player_trade_sessions')->where('id', $trade->id)->first());
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 85]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 115]);
        $this->assertSame(2, (int) DB::table('backpacks')->where('user_id', 2)->sum('count'));
        Event::assertNotDispatched(PlayerTradeChanged::class);
    }

    public function test_exact_expiration_closes_only_an_open_trade_without_transferring_anything(): void
    {
        $trade = $this->seedRewardTradeSession();
        DB::table('player_trade_sessions')->where('id', $trade->id)->update(['expires_at' => now()]);
        $state = app(PlayerTradeService::class)->state($this->economyUser(), $trade->uuid);
        $this->assertSame('cancelled', $state['status']);
        $this->assertFalse($state['initiator_confirmed']);
        $this->assertFalse($state['target_confirmed']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count'));
    }

    public function test_trade_overflow_rejects_all_money_items_and_final_confirmation(): void
    {
        $trade = $this->seedRewardTradeSession();
        DB::table('users')->where('id', 2)->update(['money' => WalletBalanceLimits::MAX_BALANCE]);
        $this->rejected(fn () => app(PlayerTradeService::class)->confirm($this->economyUser(2), $trade->uuid));
        $this->assertDatabaseHas('player_trade_sessions', ['id' => $trade->id, 'status' => 'active', 'target_confirmed' => false]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => WalletBalanceLimits::MAX_BALANCE]);
        $this->assertSame(10, (int) DB::table('backpacks')->where('user_id', 1)->sum('count'));
        $this->assertDatabaseMissing('backpacks', ['user_id' => 2]);
    }

    public function test_equal_money_offers_at_the_wallet_limit_do_not_overflow_in_between(): void
    {
        $trade = $this->seedRewardTradeSession();
        DB::table('users')->whereIn('id', [1, 2])->update(['money' => WalletBalanceLimits::MAX_BALANCE]);
        $trade->update(['initiator_offer' => ['items' => [1 => 2], 'money' => 10], 'target_offer' => ['items' => [], 'money' => 10]]);
        $state = app(PlayerTradeService::class)->confirm($this->economyUser(2), $trade->uuid);
        $this->assertSame('completed', $state['status']);
        $this->assertSame(2 * WalletBalanceLimits::MAX_BALANCE, (int) DB::table('users')->whereIn('id', [1, 2])->sum('money'));
        $this->assertSame(2, (int) DB::table('backpacks')->where('user_id', 2)->sum('count'));
    }

    public function test_failed_item_transfer_rolls_back_trade_money_and_inventory(): void
    {
        $trade = $this->seedRewardTradeSession();
        DB::unprepared("CREATE TRIGGER fail_trade_item BEFORE INSERT ON backpacks WHEN NEW.user_id = 2 BEGIN SELECT RAISE(ABORT, 'Injected delivery failure'); END");
        try {
            app(PlayerTradeService::class)->confirm($this->economyUser(2), $trade->uuid);
            $this->fail('Transfer failure must abort completion.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('Injected delivery failure', $exception->getMessage());
        }
        $this->assertDatabaseHas('player_trade_sessions', ['id' => $trade->id, 'status' => 'active', 'target_confirmed' => false]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 100]);
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count'));
        $this->assertDatabaseCount('items', 1);
    }

    public function test_rolled_back_completion_does_not_publish_or_transfer_anything(): void
    {
        $trade = $this->seedRewardTradeSession();
        Event::fake([PlayerTradeChanged::class]);
        DB::beginTransaction();
        app(PlayerTradeService::class)->confirm($this->economyUser(2), $trade->uuid);
        DB::rollBack();
        $this->assertDatabaseHas('player_trade_sessions', ['id' => $trade->id, 'status' => 'active']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => 100]);
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count'));
        Event::assertNotDispatched(PlayerTradeChanged::class);
    }
}
