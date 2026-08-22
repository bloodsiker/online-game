<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence;

use App\Modules\Monster\Domain\Contracts\MonsterReadRepository;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;

class EloquentMonsterReadRepository implements MonsterReadRepository
{
    public function findByLocationMonsterId(int $locationMonsterId): ?Monster
    {
        return MonsterOnLocation::with([
            'monster.items',
            'monster.locations.map',
        ])
            ->find($locationMonsterId)
            ?->monster;
    }
}
