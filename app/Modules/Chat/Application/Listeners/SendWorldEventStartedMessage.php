<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Event\Domain\Events\WorldEventStarted;

final class SendWorldEventStartedMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(WorldEventStarted $event): void
    {
        $url = route('events', ['mode' => 'events']).'#event-run-'.$event->runId;
        $eventLink = sprintf(
            '<a href="%s" style="color:inherit;text-decoration:underline" onclick="try{window.top.toggleMap(false);window.top.toLocation(this.href,true);return false;}catch(e){}"><em>«%s»</em></a>',
            e($url),
            e($event->title),
        );
        $mapLink = '<em>«'.e($event->mapName).'»</em>';
        if ($event->mapSlug !== null) {
            $mapLink = sprintf(
                '<a href="%s" style="color:inherit;text-decoration:underline" onclick="window.open(this.href,\'\',\'width=1000,height=750,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\');return false;"><em>«%s»</em></a>',
                e(route('map.public', ['slug' => $event->mapSlug])),
                e($event->mapName),
            );
        }

        $message = sprintf(
            ':speak: Началось событие %s! Отправляйтесь на карту %s.',
            $eventLink,
            $mapLink,
        );

        $this->chatService->sendSystem(
            message: $message,
            mapId: null,
            clanId: null,
            type: ChatMessageType::WorldEvent,
        );
    }
}
