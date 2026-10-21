<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Notification;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
use App\Modules\Chat\Domain\Events\ChatMessageCreated;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Notification\Application\Services\PlayerNotificationService;
use App\Modules\Notification\Domain\Events\PlayerNotificationAvailable;
use App\Modules\Notification\Infrastructure\Persistence\Models\PlayerNotification;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

final class PlayerNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_admin')->default(false);
            $table->integer('warehouse_count')->default(50);
            $table->integer('bag_count')->default(25);
            $table->integer('slot_count')->default(3);
            $table->timestamps();
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('settings');
        });
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('channel');
            $table->string('type');
            $table->text('message');
            $table->string('translation_key')->nullable();
            $table->json('translation_params')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_17_000009_create_player_notifications_table.php'))->up();
        Event::fake([PlayerNotificationAvailable::class, ChatMessageCreated::class]);
        Queue::fake();
    }

    private function notify(User $user, string $key = 'reward:1'): void
    {
        app(PlayerNotificationService::class)->notify(
            userId: (int) $user->id, type: 'clan_boss_reward', deduplicationKey: $key,
            message: new LocalizedChatMessage('chat.clan_boss_personal_reward', [
                'boss' => ['translations' => ['ru' => 'Страж', 'uk' => 'Вартовий']],
                'rewards' => [new LocalizedChatMessage('chat.reward_money', ['amount' => 650])],
            ]),
        );
    }

    public function test_offline_notification_is_retained_until_chat_acknowledges_it(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        $service = app(PlayerNotificationService::class);
        $this->notify($user);
        $this->assertSame(0, ChatMessage::count());
        $this->travel(3)->days();
        $pending = $service->pending(user: $user);
        $notification = $pending->sole();
        $this->assertNull($notification->read_at);
        $this->assertSame(now()->getTimestamp(), $notification->delivered_at->getTimestamp());
        $this->assertSame((int) $user->id, (int) $notification->chatMessage->target_user_id);
        $this->assertSame(now()->getTimestamp(), $notification->chatMessage->created_at->getTimestamp());
        $this->assertCount(1, $service->pending(user: $user));
        $this->assertSame(1, ChatMessage::count());
        $service->acknowledge(userId: (int) $user->id, receipts: collect([
            ['id' => $notification->id, 'message_id' => $notification->chat_message_id],
        ]));
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertCount(0, $service->pending(user: $user));
        $this->travel(2)->days();
        $this->assertCount(0, $service->pending(user: $user));
        $this->assertSame(1, ChatMessage::count());
    }

    public function test_duplicate_event_creates_only_one_notification_and_private_signal(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        $this->notify($user);
        $this->notify($user);
        $this->assertSame(1, PlayerNotification::count());
        Event::assertDispatchedTimes(PlayerNotificationAvailable::class, 1);
        $event = new PlayerNotificationAvailable((int) $user->id);
        $this->assertSame('private-App.Models.User.'.$user->id, $event->broadcastOn()->name);
        $this->assertSame([], $event->broadcastWith());
    }

    public function test_expired_unacknowledged_chat_message_is_recreated_without_regranting_reward(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        $this->notify($user);
        $service = app(PlayerNotificationService::class);
        $first = $service->pending(user: $user)->sole();
        $oldMessageId = $first->chat_message_id;
        $this->travel(11)->minutes();
        $second = $service->pending(user: $user)->sole();
        $this->assertNotSame($oldMessageId, $second->chat_message_id);
        $this->assertSame(1, PlayerNotification::count());
        $service->acknowledge(userId: (int) $user->id, receipts: collect([
            ['id' => $first->id, 'message_id' => $oldMessageId],
        ]));
        $this->assertNull($second->fresh()->read_at);
        $this->assertSame(2, ChatMessage::count());
    }

    public function test_other_player_cannot_receive_or_acknowledge_someones_notification(): void
    {
        $owner = User::forceCreate(['name' => 'Владелец']);
        $other = User::forceCreate(['name' => 'Другой']);
        $this->notify($owner);
        $service = app(PlayerNotificationService::class);
        $notification = $service->pending(user: $owner)->sole();
        $this->assertCount(0, $service->pending(user: $other));
        $this->withoutMiddleware()->actingAs($other)->postJson(route('player.notifications.acknowledge'), [
            'receipts' => [['id' => $notification->id, 'message_id' => $notification->chat_message_id]],
        ])->assertOk();
        $this->assertNull($notification->fresh()->read_at);
        $this->postJson(route('player.notifications.acknowledge'), ['receipts' => []])->assertUnprocessable();
    }

    public function test_messages_keep_translation_payload_until_delivery(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        $this->notify($user);
        $message = app(PlayerNotificationService::class)->pending(user: $user)->sole()->chatMessage;
        $localized = new LocalizedChatMessage(key: $message->translation_key, params: $message->translation_params);
        $translator = app(ChatMessageTranslator::class);
        $this->assertStringContainsString('Страж', $translator->render($localized, 'ru'));
        $this->assertStringContainsString('Вартовий', $translator->render($localized, 'uk'));
        $this->assertStringContainsString('650', $translator->render($localized, 'uk'));
    }

    public function test_pending_endpoint_returns_localized_chat_messages_and_accepts_owner_acknowledgement(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        DB::table('user_game_settings')->insert(['user_id' => $user->id, 'settings' => '{"language":"uk"}']);
        $this->notify($user);
        $this->withoutMiddleware()->actingAs($user);
        $response = $this->getJson(route('player.notifications'))->assertOk()
            ->assertJsonCount(1)->assertJsonPath('0.message.type', 'information')
            ->assertJsonPath('0.message.target_user_id', (int) $user->id);
        $this->assertStringContainsString('Вартовий', $response->json('0.message.content'));
        $this->postJson(route('player.notifications.acknowledge'), ['receipts' => [[
            'id' => $response->json('0.id'), 'message_id' => $response->json('0.message.id'),
        ]]])->assertOk();
        $this->getJson(route('player.notifications'))->assertOk()->assertExactJson([]);
    }

    public function test_deleted_chat_message_is_recreated_and_missing_migration_is_safe(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        $this->notify($user);
        $service = app(PlayerNotificationService::class);
        $messageId = $service->pending(user: $user)->sole()->chat_message_id;
        ChatMessage::whereKey($messageId)->delete();
        $this->assertNotSame($messageId, $service->pending(user: $user)->sole()->chat_message_id);
        Schema::drop('player_notifications');
        $this->assertCount(0, $service->pending(user: $user));
        $this->assertFalse($service->notify(userId: (int) $user->id, type: 'test', deduplicationKey: 'test', message: new LocalizedChatMessage('chat.reward_money', ['amount' => 1])));
    }

    public function test_rollback_does_not_publish_or_keep_notifications(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        try {
            DB::transaction(function () use ($user): void {
                $this->notify($user);
                Event::assertNotDispatched(PlayerNotificationAvailable::class);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }
        $this->assertSame(0, PlayerNotification::count());
        Event::assertNotDispatched(PlayerNotificationAvailable::class);
    }

    public function test_backlog_is_delivered_in_batches_of_fifty(): void
    {
        $user = User::forceCreate(['name' => 'Игрок']);
        for ($i = 0; $i < 55; $i++) {
            $this->notify($user, 'reward:'.$i);
        }
        $service = app(PlayerNotificationService::class);
        $batch = $service->pending(user: $user);
        $this->assertCount(50, $batch);
        $service->acknowledge(userId: (int) $user->id, receipts: $batch->map(fn ($entry) => [
            'id' => $entry->id, 'message_id' => $entry->chat_message_id,
        ]));
        $this->assertCount(5, $service->pending(user: $user));
    }
}
