<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Moderation\Domain\Events\ChatMuteImposed;

final readonly class SendChatMuteSystemMessage
{
    public function __construct(private ChatService $chatService) {}

    public function handle(ChatMuteImposed $event): void
    {
        $reason = filled($event->reason) ? e(trim((string) $event->reason)) : 'не указана';

        $this->chatService->sendSystem(sprintf(
            'На персонажа [[user_%d]] наложено проклятие молчания на %s Причина: %s.',
            $event->userId,
            $this->durationLabel($event->durationMinutes),
            $reason,
        ));
    }

    private function durationLabel(int $totalMinutes): string
    {
        $days = intdiv($totalMinutes, 1440);
        $hours = intdiv($totalMinutes % 1440, 60);
        $minutes = $totalMinutes % 60;
        $parts = [];

        if ($days > 0) {
            $parts[] = $days.' д.';
        }
        if ($hours > 0) {
            $parts[] = $hours.' ч.';
        }
        if ($minutes > 0) {
            $parts[] = $minutes.' мин.';
        }

        return implode(' ', $parts);
    }
}
