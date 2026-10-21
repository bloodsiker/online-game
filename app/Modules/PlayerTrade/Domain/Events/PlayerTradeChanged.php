<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade\Domain\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class PlayerTradeChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public string $uuid) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('player-trade.'.$this->uuid);
    }

    public function broadcastAs(): string
    {
        return 'player-trade.changed';
    }

    public function broadcastWith(): array
    {
        return ['uuid' => $this->uuid];
    }
}
