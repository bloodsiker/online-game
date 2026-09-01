<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Events;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ChatMessageExpired implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public readonly int $messageId;

    public readonly ChatChannel $channel;

    public readonly ?int $senderUserId;

    public readonly ?int $targetUserId;

    public function __construct(ChatMessage $message)
    {
        $this->messageId = (int) $message->id;
        $this->channel = $message->channel;
        $this->senderUserId = $message->user_id === null ? null : (int) $message->user_id;
        $this->targetUserId = $message->target_user_id === null ? null : (int) $message->target_user_id;
    }

    /** @return list<PrivateChannel> */
    public function broadcastOn(): array
    {
        if ($this->channel === ChatChannel::System) {
            return [new PrivateChannel('chat.system')];
        }

        return collect([$this->senderUserId, $this->targetUserId])
            ->filter(static fn (?int $userId): bool => $userId !== null)
            ->unique()
            ->map(static fn (int $userId): PrivateChannel => new PrivateChannel('App.Models.User.'.$userId))
            ->values()
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'chat.message.expired';
    }

    /** @return array{message_id: int, preserve_in_private: bool} */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'preserve_in_private' => $this->channel === ChatChannel::Private,
        ];
    }
}
