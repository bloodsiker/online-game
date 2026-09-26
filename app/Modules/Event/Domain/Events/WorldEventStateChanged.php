<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class WorldEventStateChanged implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly int $runId) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.system');
    }

    public function broadcastAs(): string
    {
        return 'world-event.state.changed';
    }

    /** @return array{run_id: int} */
    public function broadcastWith(): array
    {
        return ['run_id' => $this->runId];
    }
}
