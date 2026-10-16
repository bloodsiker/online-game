<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Event\Domain\Events\WorldEventStarted;

final class SendWorldEventStartedMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(WorldEventStarted $event): void
    {
        $url = route('events', ['mode' => 'events']).'#event-run-'.$event->runId;
        $eventTitle = $event->titleTranslations === null
            ? $event->title
            : ['translations' => $event->titleTranslations];
        $mapTitle = $event->mapNameTranslations === null
            ? $event->mapName
            : ['translations' => $event->mapNameTranslations];
        $eventLink = new LocalizedChatMessage('chat.event_link', ['url' => $url, 'title' => $eventTitle]);
        $mapLink = new LocalizedChatMessage('chat.map_name', ['title' => $mapTitle]);
        if ($event->mapSlug !== null) {
            $mapLink = new LocalizedChatMessage('chat.map_link', ['url' => route('map.public', ['slug' => $event->mapSlug]), 'title' => $mapTitle]);
        }

        $message = new LocalizedChatMessage('chat.world_event_started', ['event' => $eventLink, 'map' => $mapLink]);

        $this->chatService->sendSystem(
            message: $message,
            mapId: null,
            clanId: null,
            type: ChatMessageType::WorldEvent,
        );
    }
}
