<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Босс пришёл на локацию или ушёл с неё. Слышат только игроки,
 * стоящие на этой локации (канал location.{id}).
 */
final class BossMoved implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const ARRIVED = 'arrived';

    public const LEFT = 'left';

    /**
     * @param  array<string, string>  $names  название босса по языкам (ru, uk)
     * @param  ?string  $direction  north|south|east|west|up|down: откуда пришёл / куда ушёл
     */
    public function __construct(
        public readonly int $locationId,
        public readonly string $type,
        public readonly array $names,
        public readonly ?string $direction,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('location.'.$this->locationId);
    }

    public function broadcastAs(): string
    {
        return 'boss.moved';
    }

    /** @return array{type: string, names: array<string, string>, direction: ?string} */
    public function broadcastWith(): array
    {
        return ['type' => $this->type, 'names' => $this->names, 'direction' => $this->direction];
    }
}
