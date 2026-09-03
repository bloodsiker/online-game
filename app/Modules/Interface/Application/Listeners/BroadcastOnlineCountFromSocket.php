<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Listeners;

use App\Modules\Interface\Domain\Events\OnlineCountUpdated;
use Laravel\Reverb\Events\ChannelRemoved;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;

final class BroadcastOnlineCountFromSocket
{
    public function __construct(private readonly ChannelManager $channels) {}

    public function handle(MessageReceived|ChannelRemoved $event): void
    {
        if ($event instanceof ChannelRemoved) {
            if ($event->channel->name() === 'presence-online') {
                OnlineCountUpdated::dispatch(0);
            }

            return;
        }

        $payload = json_decode($event->message, true);
        if (! is_array($payload)) {
            return;
        }

        $eventName = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }

        $channelName = $payload['channel'] ?? ($data['channel'] ?? null);
        $isCountRequest = in_array($eventName, ['client-online-count-request', 'client-online-count-sync'], true)
            && in_array($channelName, ['online-count', 'presence-online'], true);
        $isPresenceLeave = $eventName === 'pusher:unsubscribe' && $channelName === 'presence-online';

        if (! $isCountRequest && ! $isPresenceLeave) {
            return;
        }

        $manager = $this->channels->for($event->connection->app());
        if ($isCountRequest && ! $manager->find((string) $channelName)?->subscribed($event->connection)) {
            return;
        }

        $presence = $manager->find('presence-online');
        $count = (int) ($presence?->data()['presence']['count'] ?? 0);
        OnlineCountUpdated::dispatch($count);
    }
}
