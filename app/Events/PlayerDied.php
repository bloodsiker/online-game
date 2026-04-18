<?php

namespace App\Events;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerDied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Player $player) {}
}
