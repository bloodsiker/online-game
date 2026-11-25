<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatDurationFormatter;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Moderation\Domain\Events\ChatMuteImposed;

final readonly class SendChatMuteSystemMessage
{
    public function __construct(private ChatService $chatService) {}

    public function handle(ChatMuteImposed $event): void
    {
        $reason = filled($event->reason) ? trim((string) $event->reason) : new LocalizedChatMessage('chat.no_reason');
        $duration = ChatDurationFormatter::parts($event->durationMinutes);

        $this->chatService->sendSystem(new LocalizedChatMessage('chat.mute', [
            'user_id' => $event->userId,
            'duration' => $duration,
            'reason' => $reason,
        ]));
    }
}
