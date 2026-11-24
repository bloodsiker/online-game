<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Contracts;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;

interface PlayerBattleStatus
{
    /** Участвует ли живой игрок в активном бою на своей текущей локации. */
    public function isInBattle(Player $player): bool;
}
