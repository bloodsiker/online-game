<?php

namespace App\Services;

use App\Models\Player\Player;

class ExperienceService
{
    public const DEATH_PENALTY_EXPERIENCE = 0.1;

    public function lostExpAfterDeath(Player $player): void
    {
        $lostExp = round($player->exp_diff * self::DEATH_PENALTY_EXPERIENCE);

        $player->user->location_id = $player->user->currentLocation->map->resp_location_id;

        $player->death++;
        $player->hp_now = $player->hp_max;
        $player->mp_now = $player->mp_max;
        $player->exp = max($player->exp_up - $player->exp_diff, $player->exp - $lostExp);
        $player->push();
    }
}
