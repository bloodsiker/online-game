<?php

namespace App\Modules\Player\Domain\Events;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerChangeStat
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Player $player;

    /** true — полностью восстановить HP/MP (повышение уровня); false — оставить текущие, обрезав по новому максимуму. */
    public bool $restoreResources;

    public function __construct(Player $player, bool $restoreResources = true)
    {
        $this->player = $player;
        $this->restoreResources = $restoreResources;
    }
}
