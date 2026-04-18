<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence;

use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class EloquentPlayerRepository implements PlayerRepositoryInterface
{
    public function save(Player $player): void
    {
        $player->save();
    }
}
