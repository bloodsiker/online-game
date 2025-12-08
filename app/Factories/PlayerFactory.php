<?php

namespace  App\Factories;

use App\Models\Experience;
use App\Models\Player\Player;
use App\Models\Player\PlayerEquipment;
use App\Models\User;

class PlayerFactory
{
    public function create(User $user, int $raceId): Player
    {
        $exp = Experience::where('lvl', 1)->firstOrFail();

        $player = Player::create([
            'user_id' => $user->id,
            'race_id' => $raceId,
            'lvl' => 1,
            'exp' => 0,
            'exp_up' => $exp->exp + $exp->exp_diff,
            'exp_diff' => $exp->exp_diff,
            'str' => 1,
            'agil' => 1,
            'int' => 1,
            'mud' => 1,
            'intel' => 1,
            'hp_now' => 10,
            'hp_max' => 10,
            'mp_now' => 0,
            'mp_max' => 0,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'dodge' => 0,
            'critical' => 0,
            'free_stats' => 5,
            'victory' => 0,
            'death' => 0,
            'is_main' => true,
            'is_active' => true,
        ]);

        PlayerEquipment::create([
            'player_id' => $player->id,
        ]);

        $user->update(['player_id' => $player->id]);

        return $player;
    }
}
