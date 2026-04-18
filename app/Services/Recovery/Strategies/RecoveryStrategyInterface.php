<?php

namespace App\Services\Recovery\Strategies;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Services\Recovery\Dto\RecoveryResultDto;

interface RecoveryStrategyInterface
{
    public function recover(Player $player, Structure $structure): RecoveryResultDto;
}
