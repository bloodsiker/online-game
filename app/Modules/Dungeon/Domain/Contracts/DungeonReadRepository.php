<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Contracts;

use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use Illuminate\Support\Collection;

interface DungeonReadRepository
{
    /**
     * @return Collection<int, Dungeon>
     */
    public function getActive(): Collection;

    public function findActiveByIdOrFail(int $dungeonId): Dungeon;

    public function findByIdOrFail(int $dungeonId): Dungeon;
}
