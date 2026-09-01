<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Jobs\BroadcastChatMessageExpiration;
use App\Modules\Chat\Application\Listeners\SendQuestItemDropMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Application\UseCases\SendSystemMessage;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Events\ChatMessageCreated;
use App\Modules\Chat\Domain\Events\ChatMessageExpired;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Infrastructure\Persistence\EloquentChatMessageRepository;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\Quest\Domain\Events\QuestItemDropped;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class QuestItemNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('channel', 20);
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->unsignedBigInteger('clan_id')->nullable();
            $table->unsignedBigInteger('map_id')->nullable();
            $table->text('message');
            $table->string('type', 20);
            $table->timestamps();
        });
    }

    public function test_quest_item_notification_is_visible_only_to_its_recipient_in_main_chat(): void
    {
        Event::fake([ChatMessageCreated::class]);
        Bus::fake([BroadcastChatMessageExpiration::class]);

        DB::connection('sqlite')->table('users')->insert([
            ['id' => 1, 'name' => 'Победитель'],
            ['id' => 2, 'name' => 'Другой игрок'],
        ]);

        $friendRepository = Mockery::mock(FriendRelationshipRepository::class);
        $friendRepository->shouldReceive('getIgnoredUserIdsByPlayerId')
            ->with(101)
            ->once()
            ->andReturn([]);
        $friendRepository->shouldReceive('getIgnoredUserIdsByPlayerId')
            ->with(202)
            ->once()
            ->andReturn([]);

        $partyRepository = Mockery::mock(PartyRepositoryInterface::class);
        $repository = new EloquentChatMessageRepository($friendRepository, $partyRepository);
        $sender = new SendSystemMessage($repository);

        $recipient = new User;
        $recipient->id = 1;
        $recipient->player_id = 101;

        $otherUser = new User;
        $otherUser->id = 2;
        $otherUser->player_id = 202;

        $message = $sender->toUser(
            $recipient,
            'С монстра выпал квестовый предмет: [[share_item_42]]',
            ChatMessageType::QuestItem,
        );

        $recipientMessages = $repository->getForChannel($recipient, ChatChannel::Main, null, 60);
        $otherUserMessages = $repository->getForChannel($otherUser, ChatChannel::Main, null, 60);

        $this->assertSame(ChatChannel::Main, $message->channel);
        $this->assertSame(ChatMessageType::QuestItem, $message->type);
        $this->assertSame($recipient->id, $message->target_user_id);
        $this->assertCount(1, $recipientMessages);
        $this->assertSame($message->id, $recipientMessages[0]->id);
        $this->assertCount(0, $otherUserMessages);
        Event::assertDispatched(
            ChatMessageCreated::class,
            fn (ChatMessageCreated $event): bool => $event->messageId === $message->id
                && $event->targetUserId === $recipient->id,
        );
        Bus::assertDispatched(
            BroadcastChatMessageExpiration::class,
            fn (BroadcastChatMessageExpiration $job): bool => $job->messageId === $message->id,
        );

        Event::fake([ChatMessageExpired::class]);
        (new BroadcastChatMessageExpiration((int) $message->id))->handle();

        Event::assertDispatched(
            ChatMessageExpired::class,
            fn (ChatMessageExpired $event): bool => $event->messageId === $message->id,
        );
        $this->assertTrue(ChatMessage::query()->whereKey($message->id)->exists());
    }

    public function test_quest_item_drop_event_is_dispatched_only_after_successful_transaction_commit(): void
    {
        Event::fake([QuestItemDropped::class]);

        DB::connection('sqlite')->beginTransaction();
        QuestItemDropped::dispatch(new User, 42);

        Event::assertNotDispatched(QuestItemDropped::class);

        DB::connection('sqlite')->rollBack();

        Event::assertNotDispatched(QuestItemDropped::class);

        DB::connection('sqlite')->beginTransaction();
        QuestItemDropped::dispatch(new User, 42);

        Event::assertNotDispatched(QuestItemDropped::class);

        DB::connection('sqlite')->commit();

        Event::assertDispatchedTimes(QuestItemDropped::class, 1);
    }

    public function test_quest_item_drop_listener_sends_personal_message_with_share_item_shortcode(): void
    {
        $recipient = new User;
        $recipient->id = 1;

        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendQuestItemDropToUser')
            ->once()
            ->with($recipient, 'С монстра выпал квестовый предмет: [[share_item_42]]')
            ->andReturn(new ChatMessage);

        $listener = new SendQuestItemDropMessage($chatService);
        $listener->handle(new QuestItemDropped($recipient, 42));
    }
}
