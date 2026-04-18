<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\Contracts;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;

interface MonsterReadRepository
{
    public function findByLocationMonsterId(int $locationMonsterId): ?Monster;
}
