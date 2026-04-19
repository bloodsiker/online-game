<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Factories;

use App\Modules\Player\Application\DTOs\InitialExperienceDTO;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class PlayerFactory
{
    public function create(User $user, int $raceId, InitialExperienceDTO $experience): Player
    {
        return (new Player)->forceFill([
            'user_id' => $user->id,
            'race_id' => $raceId,
            'lvl' => 1,
            'exp' => 0,
            'exp_up' => $experience->expUp,
            'exp_diff' => $experience->expDiff,
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'hp_now' => 10,
            'hp_max' => 10,
            'mp_now' => 0,
            'mp_max' => 0,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'free_stats' => 5,
            'victory' => 0,
            'death' => 0,
            'is_main' => true,
            'is_active' => true,
        ]);
    }
}
