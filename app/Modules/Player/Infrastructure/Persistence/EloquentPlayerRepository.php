<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence;

use App\Models\Player\Player;
use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;

class EloquentPlayerRepository implements PlayerRepositoryInterface
{
    public function save(Player $player): void
    {
        $player->save();
    }
}