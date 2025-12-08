<?php

namespace App\Services\Recovery\Strategies;

use App\Models\Player\Player;
use App\Models\Structure;
use App\Services\Recovery\Dto\RecoveryResultDto;

class FullHealStrategy implements RecoveryStrategyInterface
{
    public function recover(Player $player, Structure $structure): RecoveryResultDto
    {
        $healHp = $player->hp_max - $player->hp_now;
        $healMp = $player->mp_max - $player->mp_now;
        $player->hp_now = $player->hp_max;
        $player->mp_now = $player->mp_max;
        $player->save();

        return new RecoveryResultDto($player, $healHp, $healMp, []);
    }
}
