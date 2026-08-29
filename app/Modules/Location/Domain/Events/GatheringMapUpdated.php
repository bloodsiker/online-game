<?php

declare(strict_types=1);

namespace App\Modules\Location\Domain\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class GatheringMapUpdated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $mapId,
        public readonly ?int $nodeId,
        public readonly string $reason,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('gathering.map.'.$this->mapId)];
    }

    public function broadcastAs(): string
    {
        return 'gathering.map.updated';
    }

    /**
     * @return array{mapId: int, nodeId: int|null, reason: string, emittedAt: string}
     */
    public function broadcastWith(): array
    {
        return [
            'mapId' => $this->mapId,
            'nodeId' => $this->nodeId,
            'reason' => $this->reason,
            'emittedAt' => now()->toIso8601String(),
        ];
    }
}
