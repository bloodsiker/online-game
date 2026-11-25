<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatDurationFormatter;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Structure\Prison\Domain\Events\PlayerDetained;

/** Объявление в системном чате о заключении в Темницу — как у проклятия молчания. */
final readonly class SendDetentionSystemMessage
{
    public function __construct(private ChatService $chatService) {}

    public function handle(PlayerDetained $event): void
    {
        $reason = filled($event->reason) ? trim((string) $event->reason) : new LocalizedChatMessage('chat.no_reason');

        $this->chatService->sendSystem($event->durationMinutes === null
            ? new LocalizedChatMessage('chat.detention_indefinite', [
                'user_id' => $event->userId,
                'reason' => $reason,
            ])
            : new LocalizedChatMessage('chat.detention', [
                'user_id' => $event->userId,
                'duration' => ChatDurationFormatter::parts($event->durationMinutes),
                'reason' => $reason,
            ]));
    }
}
