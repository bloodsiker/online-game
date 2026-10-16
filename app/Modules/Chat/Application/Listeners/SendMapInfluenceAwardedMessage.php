<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Influence\Domain\Events\MapInfluenceAwarded;

final readonly class SendMapInfluenceAwardedMessage
{
    public function __construct(private ChatService $chatService) {}

    public function handle(MapInfluenceAwarded $event): void
    {
        $this->chatService->sendQuestToUser(
            $event->user,
            new LocalizedChatMessage('chat.influence', ['map' => $event->mapName, 'amount' => $event->amount]),
        );
    }
}
