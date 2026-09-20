<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Listeners\SendChatMuteSystemMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Moderation\Domain\Events\ChatMuteImposed;
use Mockery;
use Tests\TestCase;

final class ChatMuteNotificationTest extends TestCase
{
    public function test_listener_sends_global_system_message_with_duration_and_reason(): void
    {
        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendSystem')
            ->once()
            ->with('На персонажа [[user_17]] наложено проклятие молчания на 1 д. 2 ч. 5 мин. Причина: Флуд.')
            ->andReturn(new ChatMessage);

        (new SendChatMuteSystemMessage($chatService))->handle(
            new ChatMuteImposed(17, 1565, 'Флуд'),
        );
    }

    public function test_listener_uses_fallback_when_reason_is_empty(): void
    {
        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendSystem')
            ->once()
            ->with('На персонажа [[user_9]] наложено проклятие молчания на 30 мин. Причина: не указана.')
            ->andReturn(new ChatMessage);

        (new SendChatMuteSystemMessage($chatService))->handle(
            new ChatMuteImposed(9, 30, null),
        );
    }
}
