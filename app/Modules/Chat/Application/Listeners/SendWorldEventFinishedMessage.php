<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Event\Domain\Events\WorldEventFinished;

final class SendWorldEventFinishedMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(WorldEventFinished $event): void
    {
        $this->chatService->sendSystem(
            message: new LocalizedChatMessage('chat.world_event_finished', ['title' => $event->titleTranslations === null
                ? $event->title
                : ['translations' => $event->titleTranslations]]),
            mapId: null,
            clanId: null,
            type: ChatMessageType::WorldEvent,
        );
    }
}
