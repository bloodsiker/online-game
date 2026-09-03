<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Interface;

use App\Modules\Interface\Application\Listeners\BroadcastOnlineCountFromSocket;
use App\Modules\Interface\Domain\Events\OnlineCountUpdated;
use Illuminate\Support\Facades\Event;
use Laravel\Reverb\Application;
use Laravel\Reverb\Connection;
use Laravel\Reverb\Contracts\WebSocketConnection;
use Laravel\Reverb\Events\ChannelRemoved;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Protocols\Pusher\Channels\Channel;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Mockery;
use Tests\TestCase;

class OnlineCountBroadcastTest extends TestCase
{
    public function test_subscribed_client_can_request_current_presence_count(): void
    {
        Event::fake([OnlineCountUpdated::class]);
        $application = new Application('test-app', 'key', 'secret', 60, 30, ['*'], 10_000);
        $socket = new class implements WebSocketConnection
        {
            public function id(): int|string
            {
                return 1;
            }

            public function send(mixed $message): void {}

            public function close(mixed $message = null): void {}
        };
        $connection = new Connection($socket, $application, null);
        $requestChannel = Mockery::mock(Channel::class);
        $requestChannel->shouldReceive('subscribed')->once()->with($connection)->andReturnTrue();
        $presenceChannel = Mockery::mock(Channel::class);
        $presenceChannel->shouldReceive('data')->once()->andReturn(['presence' => ['count' => 3]]);
        $channels = Mockery::mock(ChannelManager::class);
        $channels->shouldReceive('for')->once()->with($application)->andReturnSelf();
        $channels->shouldReceive('find')->once()->with('online-count')->andReturn($requestChannel);
        $channels->shouldReceive('find')->once()->with('presence-online')->andReturn($presenceChannel);

        (new BroadcastOnlineCountFromSocket($channels))->handle(new MessageReceived($connection, json_encode([
            'event' => 'client-online-count-request',
            'channel' => 'online-count',
            'data' => [],
        ], JSON_THROW_ON_ERROR)));

        Event::assertDispatched(OnlineCountUpdated::class, fn (OnlineCountUpdated $event): bool => $event->count === 3);
    }

    public function test_removing_last_presence_channel_broadcasts_zero(): void
    {
        Event::fake([OnlineCountUpdated::class]);
        $channel = Mockery::mock(Channel::class);
        $channel->shouldReceive('name')->once()->andReturn('presence-online');

        (new BroadcastOnlineCountFromSocket(Mockery::mock(ChannelManager::class)))
            ->handle(new ChannelRemoved($channel));

        Event::assertDispatched(OnlineCountUpdated::class, fn (OnlineCountUpdated $event): bool => $event->count === 0);
    }
}
