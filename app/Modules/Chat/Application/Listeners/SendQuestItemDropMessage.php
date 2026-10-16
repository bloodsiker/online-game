<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Quest\Domain\Events\QuestItemDropped;

final readonly class SendQuestItemDropMessage
{
    public function __construct(
        private ChatService $chatService,
    ) {}

    public function handle(QuestItemDropped $event): void
    {
        $this->chatService->sendQuestItemDropToUser(
            $event->recipient,
            new LocalizedChatMessage('chat.quest_item', ['item_id' => $event->shareItemId]),
        );
    }
}
