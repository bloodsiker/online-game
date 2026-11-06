<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\PlayerTrade\Domain\Events\PlayerTradeChanged;
use App\Modules\PlayerTrade\Infrastructure\Persistence\Models\PlayerTradeSession;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;

trait CreatesRewardTradeFixture
{
    use CreatesInventoryFixture;

    private function createRewardTradeTables(): void
    {
        $this->createInventoryTables();
        Schema::table('players', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('lvl')->default(10);
        });
        Schema::table('backpacks', fn (Blueprint $table) => $table->integer('sort_order')->default(0));
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->text('message')->nullable();
        });
        foreach (['2026_03_31_000003_create_referral_tables.php', '2026_10_16_000001_create_player_trade_sessions_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        (require database_path('migrations/2026_11_03_000004_add_wallet_balance_constraints.php'))->up();
    }

    private function seedRewardTradeFixture(): void
    {
        $this->seedInventoryFixture();
        DB::table('players')->where('id', 1)->update(['user_id' => 1]);
        foreach ([2, 3] as $id) {
            DB::table('players')->insert(['id' => $id, 'user_id' => $id]);
            DB::table('users')->where('id', $id)->update(['player_id' => $id]);
        }
        DB::table('referrals')->insert(['id' => 1, 'referrer_user_id' => 1, 'referred_user_id' => 2]);
        DB::table('referral_reward_stages')->insert([
            ['id' => 1, 'level_threshold' => 5, 'reward_type' => 'gold', 'reward_value' => 50, 'reward_item_id' => null],
            ['id' => 2, 'level_threshold' => 10, 'reward_type' => 'diamond', 'reward_value' => 7, 'reward_item_id' => null],
            ['id' => 3, 'level_threshold' => 10, 'reward_type' => 'item', 'reward_value' => 3, 'reward_item_id' => 1],
        ]);
    }

    private function bindRewardTradeDoubles(): void
    {
        Event::fake([PlayerTradeChanged::class]);
        $chat = Mockery::mock(ChatService::class);
        $chat->shouldReceive('removeSystemMessageForUser')->andReturnNull();
        app()->instance(ChatService::class, $chat);
    }

    private function seedRewardTradeSession(bool $items = true): PlayerTradeSession
    {
        return PlayerTradeSession::create([
            'uuid' => (string) Str::uuid(), 'initiator_user_id' => 1, 'target_user_id' => 2,
            'status' => PlayerTradeSession::ACTIVE,
            'initiator_offer' => ['items' => $items ? [1 => 2] : [], 'money' => 20],
            'target_offer' => ['items' => [], 'money' => 5],
            'initiator_confirmed' => true, 'target_confirmed' => false, 'expires_at' => now()->addMinutes(30),
        ]);
    }
}
