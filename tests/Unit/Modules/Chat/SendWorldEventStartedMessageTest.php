<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\Listeners\SendWorldEventStartedMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Event\Domain\Events\WorldEventStarted;
use Mockery;
use Tests\TestCase;

final class SendWorldEventStartedMessageTest extends TestCase
{
    public function test_it_sends_global_information_message_when_event_starts(): void
    {
        $url = route('events', ['mode' => 'events']).'#event-run-25';
        $link = '<a href="'.$url.'" style="color:inherit;text-decoration:underline" onclick="try{window.top.toggleMap(false);window.top.toLocation(this.href,true);return false;}catch(e){}"><em>«Нашествие»</em></a>';
        $mapUrl = route('map.public', ['slug' => 'sewer']);
        $mapLink = '<a href="'.$mapUrl.'" style="color:inherit;text-decoration:underline" onclick="window.open(this.href,\'\',\'width=1000,height=750,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false;"><em>«Канализация»</em></a>';
        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('sendSystem')
            ->once()
            ->with(
                ':speak: Началось событие '.$link.'! Отправляйтесь на карту '.$mapLink.'.',
                null,
                null,
                ChatMessageType::WorldEvent,
            )
            ->andReturn(new ChatMessage);

        (new SendWorldEventStartedMessage($chatService))->handle(new WorldEventStarted(
            eventId: 10,
            runId: 25,
            title: 'Нашествие',
            mapName: 'Канализация',
            mapSlug: 'sewer',
        ));
    }
}
