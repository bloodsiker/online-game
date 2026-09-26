<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Event;

use App\Modules\Event\Domain\Events\WorldEventStateChanged;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use PHPUnit\Framework\TestCase;

final class WorldEventStateChangedTest extends TestCase
{
    public function test_it_broadcasts_widget_refresh_signal_to_system_chat_channel(): void
    {
        $event = new WorldEventStateChanged(42);
        $channel = $event->broadcastOn();

        $this->assertInstanceOf(ShouldBroadcastNow::class, $event);
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);
        $this->assertInstanceOf(PrivateChannel::class, $channel);
        $this->assertSame('private-chat.system', $channel->name);
        $this->assertSame('world-event.state.changed', $event->broadcastAs());
        $this->assertSame(['run_id' => 42], $event->broadcastWith());
    }
}
