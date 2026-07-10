<?php

namespace App\Listeners;

use App\Events\PlayerLeveledUp;
use App\Modules\Chat\Application\Services\ChatService;

class SendLevelUpSystemMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(PlayerLeveledUp $event): void
    {
        $player = $event->player;

        $this->chatService->sendSystem("Уровень [[user_{$player->user_id}]] увеличен до {$player->lvl}!");
    }
}
