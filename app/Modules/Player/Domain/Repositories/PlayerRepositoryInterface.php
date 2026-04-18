<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Repositories;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;

interface PlayerRepositoryInterface
{
    public function save(Player $player): void;
}
