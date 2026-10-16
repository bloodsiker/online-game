<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Reputation\Domain\Events\ReputationMedalEarned;

class SendMedalEarnedSystemMessage
{
    public function __construct(private readonly ChatService $chatService) {}

    public function handle(ReputationMedalEarned $event): void
    {
        $player = $event->player;

        $this->chatService->sendSystem(
            new LocalizedChatMessage($event->isFeat ? 'chat.medal_feat' : 'chat.medal', [
                'user_id' => $player->user_id,
                'medal' => LocalizedChatMessage::contentName($event->tier, $event->isFeat ? 'feat_medal_name' : 'medal_name'),
                'reputation' => LocalizedChatMessage::contentName($event->tier->reputation),
            ])
        );
    }
}
