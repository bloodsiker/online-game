<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence;

use App\Modules\Dungeon\Domain\Contracts\DungeonReadRepository;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use Illuminate\Support\Collection;

class EloquentDungeonReadRepository implements DungeonReadRepository
{
    public function getActive(): Collection
    {
        return Dungeon::query()
            ->with('entryItem')
            ->where('is_active', true)
            ->get();
    }

    public function findActiveByIdOrFail(int $dungeonId): Dungeon
    {
        return Dungeon::query()
            ->with('entryItem')
            ->where('is_active', true)
            ->findOrFail($dungeonId);
    }

    public function findByIdOrFail(int $dungeonId): Dungeon
    {
        return Dungeon::query()
            ->with('entryItem')
            ->findOrFail($dungeonId);
    }
}
