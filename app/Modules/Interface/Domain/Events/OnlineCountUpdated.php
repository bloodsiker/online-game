<?php

declare(strict_types=1);

namespace App\Modules\Interface\Domain\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

final class OnlineCountUpdated implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(public readonly int $count) {}

    public function broadcastOn(): Channel
    {
        return new Channel('online-count');
    }

    public function broadcastAs(): string
    {
        return 'online.count.updated';
    }

    public function broadcastWith(): array
    {
        return ['count' => $this->count];
    }
}
