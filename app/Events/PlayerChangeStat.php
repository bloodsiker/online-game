<?php

namespace App\Events;

use App\Models\Player\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerChangeStat
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Player $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }
}
