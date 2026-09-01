<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Events;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ChatMessageCreated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public readonly int $messageId;

    public readonly ChatChannel $channel;

    public readonly ?int $senderUserId;

    public readonly ?int $targetUserId;

    public readonly ?int $mapId;

    public readonly ?int $clanId;

    public readonly ?int $partyId;

    public function __construct(ChatMessage $message)
    {
        $this->messageId = (int) $message->id;
        $this->channel = $message->channel;
        $this->senderUserId = $message->user_id === null ? null : (int) $message->user_id;
        $this->targetUserId = $message->target_user_id === null ? null : (int) $message->target_user_id;
        $this->mapId = $message->map_id === null ? null : (int) $message->map_id;
        $this->clanId = $message->clan_id === null ? null : (int) $message->clan_id;
        $this->partyId = $message->party_id === null ? null : (int) $message->party_id;
    }

    /** @return list<PrivateChannel> */
    public function broadcastOn(): array
    {
        return array_map(
            static fn (string $name): PrivateChannel => new PrivateChannel($name),
            $this->channelNames(),
        );
    }

    public function broadcastAs(): string
    {
        return 'chat.message.created';
    }

    /** @return array{message_id: int, channel: string, emitted_at: string} */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'channel' => $this->channel->value,
            'emitted_at' => now()->toIso8601String(),
        ];
    }

    /** @return list<string> */
    private function channelNames(): array
    {
        if ($this->channel === ChatChannel::Private || $this->targetUserId !== null) {
            return collect([$this->senderUserId, $this->targetUserId])
                ->filter(static fn (?int $userId): bool => $userId !== null)
                ->unique()
                ->map(static fn (int $userId): string => 'App.Models.User.'.$userId)
                ->values()
                ->all();
        }

        $name = match ($this->channel) {
            ChatChannel::Main => 'chat.main',
            ChatChannel::Trade => 'chat.trade',
            ChatChannel::Location => $this->mapId === null ? null : 'chat.location.'.$this->mapId,
            ChatChannel::Clan => $this->clanId === null ? null : 'chat.clan.'.$this->clanId,
            ChatChannel::Party => $this->partyId === null ? null : 'chat.party.'.$this->partyId,
            ChatChannel::System => 'chat.system',
            ChatChannel::Private => null,
        };

        return $name === null ? [] : [$name];
    }
}
