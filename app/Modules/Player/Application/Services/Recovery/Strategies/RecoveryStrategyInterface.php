<?php

namespace App\Modules\Player\Application\Services\Recovery\Strategies;

use App\Modules\Player\Application\Services\Recovery\Dto\RecoveryResultDto;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

interface RecoveryStrategyInterface
{
    public function recover(Player $player, Structure $structure): RecoveryResultDto;
}
