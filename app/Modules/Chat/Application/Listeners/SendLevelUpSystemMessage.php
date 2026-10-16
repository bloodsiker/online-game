<?php

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;

class SendLevelUpSystemMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(PlayerLeveledUp $event): void
    {
        $player = $event->player;

        $this->chatService->sendSystem(new LocalizedChatMessage('chat.level_up', ['user_id' => $player->user_id, 'level' => $player->lvl]));
    }
}
