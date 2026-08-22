<?php

namespace App\Modules\Player\Application\Listeners;

use App\Models\Experience;
use App\Modules\Player\Domain\Events\PlayerChangeStat;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;

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

        $player->strength = $player->strength + $race->strength;
        $player->intuition = $player->intuition + $race->intuition;
        $player->agility = $player->agility + $race->agility;
        $player->wisdom = $player->wisdom + $race->wisdom;
        $player->intelligence = $player->intelligence + $race->intelligence;
        $player->endurance = $player->endurance + $race->endurance;

        $player->save();

        event(new PlayerChangeStat($player));
    }
}
