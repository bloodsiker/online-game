<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Listeners;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;

final class UpdatePlayerPresenceFromSocket
{
    public function __construct(private readonly ChannelManager $channels) {}

    public function handle(MessageReceived $messageReceived): void
    {
        $payload = json_decode($messageReceived->message, true);

        if (! is_array($payload) || ($payload['event'] ?? null) !== 'client-player-presence') {
            return;
        }

        $channel = $payload['channel'] ?? null;
        if (! is_string($channel) || preg_match('/^private-player\.(\d+)$/', $channel, $matches) !== 1) {
            return;
        }

        $subscribedChannel = $this->channels
            ->for($messageReceived->connection->app())
            ->find($channel);

        if (! $subscribedChannel?->subscribed($messageReceived->connection)) {
            return;
        }

        User::query()
            ->where('player_id', (int) $matches[1])
            ->update(['last_online_at' => now()]);
    }
}
