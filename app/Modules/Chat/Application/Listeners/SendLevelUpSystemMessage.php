<?php

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;

class SendLevelUpSystemMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(PlayerLeveledUp $event): void
    {
        $player = $event->player;

        $this->chatService->sendSystem("Уровень [[user_{$player->user_id}]] увеличен до {$player->lvl}!");
    }
}
