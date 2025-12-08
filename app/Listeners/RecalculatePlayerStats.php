<?php

namespace App\Listeners;

use App\Events\PlayerChangeStat;
use App\Events\PlayerLeveledUp;
use App\Models\Experience;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RecalculatePlayerStats
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PlayerLeveledUp $event): void
    {
        $player = $event->player;
        $race = $player->race;

        $experience = Experience::where('lvl', $player->lvl)->first();

        $player->exp_up = $experience->exp + $experience->exp_diff;
        $player->exp_diff = $experience->exp_diff;
        $player->free_stats += $race->free_stats;

        $player->str = $player->str + $race->str;
        $player->int = $player->int + $race->int;
        $player->agil = $player->agil + $race->agil;
        $player->mud = $player->mud + $race->mud;
        $player->intel = $player->intel + $race->intel;

        $player->save();

        event(new PlayerChangeStat($player));
    }
}
