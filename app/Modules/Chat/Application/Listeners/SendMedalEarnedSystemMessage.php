<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Reputation\Domain\Events\ReputationMedalEarned;

class SendMedalEarnedSystemMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(ReputationMedalEarned $event): void
    {
        $player = $event->player;
        $verb = $event->isFeat ? 'совершил подвиг и получил медаль' : 'получил медаль';

        $this->chatService->sendSystem(
            "Игрок [[user_{$player->user_id}]] {$verb} «{$event->medalName}» ({$event->tier->reputation->name})!"
        );
    }
}
