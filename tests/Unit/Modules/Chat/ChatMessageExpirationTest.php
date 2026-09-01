<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\UseCases\GetMessages;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Chat\Domain\Services\MessageRenderer;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class ChatMessageExpirationTest extends TestCase
{
    public function test_system_messages_expire_after_thirty_minutes(): void
    {
        $message = $this->message(ChatChannel::System, ChatMessageType::System);

        $dto = $this->getMessage($message, ChatChannel::Main);

        $this->assertSame('2026-08-30T12:30:00+00:00', $dto->expires_at);
    }

    public function test_private_messages_expire_in_mixed_channels_but_remain_in_private_history(): void
    {
        $message = $this->message(ChatChannel::Private, ChatMessageType::Private);

        $this->assertSame(
            '2026-08-30T12:10:00+00:00',
            $this->getMessage($message, ChatChannel::Main)->expires_at,
        );
        $this->assertNull($this->getMessage($message, ChatChannel::Private)->expires_at);
    }

    private function getMessage(ChatMessage $message, ChatChannel $viewChannel): object
    {
        $repository = Mockery::mock(ChatMessageRepositoryInterface::class);
        $repository->shouldReceive('getForChannel')
            ->once()
            ->with(Mockery::type(User::class), $viewChannel, null, 60)
            ->andReturn([$message]);

        $user = new User;
        $user->id = 99;

        return (new GetMessages($repository, new MessageRenderer))->execute($user, $viewChannel)[0];
    }

    private function message(ChatChannel $channel, ChatMessageType $type): ChatMessage
    {
        $message = (new ChatMessage)->forceFill([
            'id' => 1,
            'channel' => $channel->value,
            'type' => $type->value,
            'user_id' => null,
            'target_user_id' => null,
            'message' => 'Тест',
            'created_at' => Carbon::parse('2026-08-30 12:00:00', 'UTC'),
        ]);
        $message->setRelation('sender', null);
        $message->setRelation('target', null);

        return $message;
    }
}
