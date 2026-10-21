<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\PlayerTrade;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\PlayerTrade\Application\Services\PlayerTradeService;
use App\Modules\PlayerTrade\Domain\Events\PlayerTradeChanged;
use App\Modules\PlayerTrade\Infrastructure\Persistence\Models\PlayerTradeSession;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Closure;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

final class PlayerTradeProximityTest extends TestCase
{
    private PlayerTradeService $service;

    private User $initiator;

    private User $target;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.player_trade_test', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('database.default', 'player_trade_test');
        DB::purge('player_trade_test');
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('money')->default(0);
            $table->timestamps();
        });
        Schema::create('player_trade_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('initiator_user_id');
            $table->unsignedBigInteger('target_user_id');
            $table->string('status');
            $table->json('initiator_offer')->nullable();
            $table->json('target_offer')->nullable();
            $table->boolean('initiator_confirmed')->default(false);
            $table->boolean('target_confirmed')->default(false);
            $table->unsignedBigInteger('chat_message_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Initiator', 'location_id' => 10, 'money' => 100],
            ['id' => 2, 'name' => 'Target', 'location_id' => 10, 'money' => 50],
        ]);
        $this->initiator = User::query()->findOrFail(1);
        $this->target = User::query()->findOrFail(2);
        $this->service = new PlayerTradeService(Mockery::mock(ChatService::class));
    }

    public function test_invitation_checks_current_locations_before_creating_a_session(): void
    {
        $this->moveTargetAway();

        $this->assertProximityRejected(fn () => $this->service->invite($this->initiator, $this->target->id));

        self::assertSame(0, PlayerTradeSession::query()->count());
    }

    public function test_invitation_cannot_be_accepted_after_the_players_separate(): void
    {
        $trade = $this->trade(['status' => PlayerTradeSession::PENDING]);
        $this->moveTargetAway();

        $this->assertProximityRejected(fn () => $this->service->accept($this->target, $trade->uuid));

        self::assertSame(PlayerTradeSession::PENDING, $trade->fresh()->status);
    }

    public function test_offer_changes_are_rejected_and_readiness_is_cleared_without_polling(): void
    {
        $trade = $this->trade(['initiator_confirmed' => true, 'target_confirmed' => true]);
        $this->moveTargetAway();

        $this->assertProximityRejected(fn () => $this->service->saveOffer($this->initiator, $trade->uuid, [], 30));

        $trade->refresh();
        self::assertFalse($trade->initiator_confirmed);
        self::assertFalse($trade->target_confirmed);
        self::assertSame(20, $trade->initiator_offer['money']);
    }

    public function test_confirmation_cannot_transfer_money_after_the_other_player_moves(): void
    {
        $trade = $this->trade(['initiator_confirmed' => true]);
        $this->moveTargetAway();

        $this->assertProximityRejected(fn () => $this->service->confirm($this->target, $trade->uuid));

        $trade->refresh();
        self::assertSame(PlayerTradeSession::ACTIVE, $trade->status);
        self::assertFalse($trade->initiator_confirmed);
        self::assertFalse($trade->target_confirmed);
        self::assertSame(100, (int) $this->initiator->fresh()->money);
        self::assertSame(50, (int) $this->target->fresh()->money);
    }

    public function test_state_blocks_both_players_and_reunion_requires_new_confirmations(): void
    {
        $trade = $this->trade(['initiator_confirmed' => true]);
        $this->moveTargetAway();

        $state = $this->service->state($this->initiator, $trade->uuid);
        self::assertFalse($state['can_trade']);
        self::assertFalse($state['initiator_confirmed']);
        self::assertStringContainsString('Target', $state['block_message']);
        self::assertStringContainsString('Initiator', $this->service->state($this->target, $trade->uuid)['block_message']);

        DB::table('users')->where('id', $this->target->id)->update(['location_id' => 10]);
        $state = $this->service->state($this->initiator, $trade->uuid);
        self::assertTrue($state['can_trade']);
        self::assertSame('', $state['block_message']);
        self::assertFalse($state['initiator_confirmed']);
        self::assertFalse($state['target_confirmed']);

        $state = $this->service->confirm($this->initiator, $trade->uuid);
        self::assertSame(PlayerTradeSession::ACTIVE, $state['status']);
        $state = $this->service->confirm($this->target, $trade->uuid);
        self::assertSame(PlayerTradeSession::COMPLETED, $state['status']);
        self::assertSame(85, (int) $this->initiator->fresh()->money);
        self::assertSame(65, (int) $this->target->fresh()->money);
    }

    private function trade(array $attributes = []): PlayerTradeSession
    {
        return PlayerTradeSession::query()->create(array_merge([
            'uuid' => (string) Str::uuid(),
            'initiator_user_id' => $this->initiator->id,
            'target_user_id' => $this->target->id,
            'status' => PlayerTradeSession::ACTIVE,
            'initiator_offer' => ['items' => [], 'money' => 20],
            'target_offer' => ['items' => [], 'money' => 5],
            'expires_at' => now()->addMinutes(30),
        ], $attributes));
    }

    public function test_non_transferable_items_are_hidden_and_cannot_be_offered(): void
    {
        $this->seedInventory();
        $trade = $this->trade(['initiator_offer' => ['items' => [1 => 1, 2 => 1], 'money' => 0]]);

        $page = $this->service->page($this->initiator, $trade->uuid);
        self::assertSame([1], $page['inventory']->pluck('id')->all());
        $state = $this->service->state($this->initiator, $trade->uuid);
        self::assertSame([101], array_column($state['initiator']['items'], 'item_id'));

        try {
            $this->service->saveOffer($this->initiator, $trade->uuid, [['backpack_id' => 2, 'count' => 1]], 0);
            self::fail('Items with is_give=0 must not be accepted in an offer.');
        } catch (RuntimeException $exception) {
            self::assertSame(__('Один из выбранных предметов больше нельзя предложить.'), $exception->getMessage());
        }
    }

    public function test_tooltip_access_is_limited_to_own_transferable_items_and_the_partner_offer(): void
    {
        $this->seedInventory();
        $trade = $this->trade(['target_offer' => ['items' => [3 => 1], 'money' => 0]]);

        self::assertSame(101, $this->service->itemForTooltip($this->initiator, $trade->uuid, 101)->id);
        self::assertSame(103, $this->service->itemForTooltip($this->initiator, $trade->uuid, 103)->id);
        foreach ([102, 104] as $id) {
            try {
                $this->service->itemForTooltip($this->initiator, $trade->uuid, $id);
                self::fail('Forbidden items and unoffered partner items must not expose tooltips.');
            } catch (ModelNotFoundException) {
                self::assertTrue(true);
            }
        }
    }

    public function test_trade_channel_is_private_and_authorizes_only_its_two_participants(): void
    {
        $trade = $this->trade();
        $authorize = Broadcast::getChannels()['player-trade.{uuid}'];
        self::assertTrue($authorize($this->initiator, $trade->uuid));
        self::assertTrue($authorize($this->target, $trade->uuid));
        self::assertFalse($authorize((new User)->forceFill(['id' => 99]), $trade->uuid));
        self::assertFalse($authorize($this->initiator, (string) Str::uuid()));

        $event = new PlayerTradeChanged($trade->uuid);
        self::assertSame('private-player-trade.'.$trade->uuid, $event->broadcastOn()->name);
        self::assertSame('player-trade.changed', $event->broadcastAs());
        self::assertSame(['uuid' => $trade->uuid], $event->broadcastWith());
        self::assertInstanceOf(ShouldBroadcast::class, $event);
        self::assertNotInstanceOf(ShouldBroadcastNow::class, $event);
    }

    public function test_committed_offer_updates_broadcast_but_rolled_back_updates_do_not(): void
    {
        $trade = $this->trade();
        Event::fake([PlayerTradeChanged::class]);
        DB::beginTransaction();
        $trade->update(['initiator_confirmed' => true]);
        Event::assertNotDispatched(PlayerTradeChanged::class);
        DB::commit();
        Event::assertDispatchedTimes(PlayerTradeChanged::class, 1);

        Event::fake([PlayerTradeChanged::class]);
        DB::beginTransaction();
        $trade->update(['target_confirmed' => true]);
        DB::rollBack();
        Event::assertNotDispatched(PlayerTradeChanged::class);
        self::assertFalse($trade->fresh()->target_confirmed);
    }

    public function test_movement_immediately_resets_readiness_and_notifies_the_trade_after_commit(): void
    {
        $trade = $this->trade(['initiator_confirmed' => true]);
        Event::fake([PlayerTradeChanged::class]);

        DB::transaction(function (): void {
            $this->target->location_id = 11;
            $this->target->save();
            Event::assertNotDispatched(PlayerTradeChanged::class);
        });

        self::assertFalse($trade->fresh()->initiator_confirmed);
        Event::assertDispatchedTimes(PlayerTradeChanged::class, 1);
        Event::assertDispatched(PlayerTradeChanged::class, fn ($event) => $event->uuid === $trade->uuid);

        $this->target->location_id = 10;
        $this->target->save();
        Event::assertDispatchedTimes(PlayerTradeChanged::class, 2);
        self::assertFalse($trade->fresh()->target_confirmed);
    }

    private function seedInventory(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->text('name');
            $table->string('image')->nullable();
            $table->boolean('is_give');
            $table->boolean('is_stackable')->default(true);
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->timestamp('expires_at')->nullable();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->integer('count')->default(1);
        });
        DB::table('share_items')->insert([
            ['id' => 1, 'name' => '{"ru":"Allowed","uk":"Allowed"}', 'is_give' => true],
            ['id' => 2, 'name' => '{"ru":"Forbidden","uk":"Forbidden"}', 'is_give' => false],
        ]);
        DB::table('items')->insert([
            ['id' => 101, 'share_item_id' => 1], ['id' => 102, 'share_item_id' => 2],
            ['id' => 103, 'share_item_id' => 1], ['id' => 104, 'share_item_id' => 1],
        ]);
        DB::table('backpacks')->insert([
            ['id' => 1, 'user_id' => 1, 'item_id' => 101], ['id' => 2, 'user_id' => 1, 'item_id' => 102],
            ['id' => 3, 'user_id' => 2, 'item_id' => 103], ['id' => 4, 'user_id' => 2, 'item_id' => 104],
        ]);
    }

    private function moveTargetAway(): void
    {
        DB::table('users')->where('id', $this->target->id)->update(['location_id' => 11]);
    }

    private function assertProximityRejected(Closure $action): void
    {
        try {
            $action();
            self::fail('Trade must be blocked when players are on different locations.');
        } catch (RuntimeException $exception) {
            self::assertSame(__('Игрок находится не рядом с вами. Торговать можно только на одной локации.'), $exception->getMessage());
        }
    }
}
