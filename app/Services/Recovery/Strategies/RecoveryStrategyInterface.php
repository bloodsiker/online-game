<?php

namespace App\Services\Recovery\Strategies;

use App\Models\Player\Player;
use App\Models\Structure;
use App\Services\Recovery\Dto\RecoveryResultDto;

interface RecoveryStrategyInterface {
    public function recover(Player $player, Structure $structure): RecoveryResultDto;
}
