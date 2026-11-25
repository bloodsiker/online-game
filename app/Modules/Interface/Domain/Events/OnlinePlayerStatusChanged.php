<?php

declare(strict_types=1);

namespace App\Modules\Interface\Domain\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class OnlinePlayerStatusChanged implements ShouldBroadcastNow, ShouldDispatchAfterCommit, ShouldRescue
{
    use Dispatchable;

    /** @param  array{is_detained?: bool, detention_release_at?: ?string, injury_expires_at?: string}  $status */
    public function __construct(private int $userId, private array $status) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('online');
    }

    public function broadcastAs(): string
    {
        return 'online.player.status.changed';
    }

    public function broadcastWith(): array
    {
        return ['user_id' => $this->userId, ...$this->status];
    }
}
