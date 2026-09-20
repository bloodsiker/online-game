<?php

declare(strict_types=1);

namespace App\Modules\Moderation\Domain\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

final class CommunicationMuteChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public readonly int $userId,
        public readonly ?string $chatMuteTitle,
        public readonly ?string $chatMuteExpiresAt,
    ) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('online');
    }

    public function broadcastAs(): string
    {
        return 'communication.mute.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'chat_mute_title' => $this->chatMuteTitle,
            'chat_mute_expires_at' => $this->chatMuteExpiresAt,
        ];
    }
}
