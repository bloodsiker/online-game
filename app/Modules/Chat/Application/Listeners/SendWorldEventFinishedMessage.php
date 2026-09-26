<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Event\Domain\Events\WorldEventFinished;

final class SendWorldEventFinishedMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(WorldEventFinished $event): void
    {
        $this->chatService->sendSystem(
            message: sprintf(':speak: Событие <em>«%s»</em> закончилось!', e($event->title)),
            mapId: null,
            clanId: null,
            type: ChatMessageType::WorldEvent,
        );
    }
}
