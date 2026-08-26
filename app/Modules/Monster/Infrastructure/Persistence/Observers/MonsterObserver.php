<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Observers;

use App\Modules\Monster\Domain\Services\MapMonstersCache;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;

class MonsterObserver
{
    public function saved(Monster $monster): void
    {
        MapMonstersCache::flush();
    }

    public function deleted(Monster $monster): void
    {
        MapMonstersCache::flush();
    }
}
