<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Listeners\SendWorldEventFinishedMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Event\Domain\Events\WorldEventFinished;
use Mockery;
use Tests\TestCase;

final class SendWorldEventFinishedMessageTest extends TestCase
{
    public function test_it_sends_global_message_when_event_finishes(): void
    {
        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendSystem')
            ->once()
            ->with(
                ':speak: Событие <em>«Складские воры»</em> закончилось!',
                null,
                null,
                ChatMessageType::WorldEvent,
            )
            ->andReturn(new ChatMessage);

        (new SendWorldEventFinishedMessage($chatService))->handle(new WorldEventFinished(
            eventId: 10,
            runId: 25,
            title: 'Складские воры',
        ));
    }
}
