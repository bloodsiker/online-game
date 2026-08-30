<?php

declare(strict_types=1);

namespace App\Modules\Interface\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PlayerStateUpdated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @param array<string, mixed> $state */
    public function __construct(
        public readonly int $playerId,
        public readonly array $state,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('player.'.$this->playerId);
    }

    public function broadcastAs(): string
    {
        return 'player.state.updated';
    }

    /** @return array{state: array<string, mixed>} */
    public function broadcastWith(): array
    {
        return ['state' => $this->state];
    }
}
