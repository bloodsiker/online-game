<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Events;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;

/** Recalculate level-dependent state synchronously inside the current transaction. */
final readonly class PlayerLevelChanged
{
    public function __construct(public Player $player) {}
}
