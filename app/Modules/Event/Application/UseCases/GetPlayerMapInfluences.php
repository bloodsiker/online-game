<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\UseCases;

use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluence;
use Illuminate\Support\Collection;

final class GetPlayerMapInfluences
{
    /** @return Collection<int, MapInfluence> */
    public function execute(int $userId): Collection
    {
        return MapInfluence::query()
            ->with('map.influenceLevels.medal')
            ->where('user_id', $userId)
            ->where('influence', '>', 0)
            ->orderByDesc('influence')
            ->orderBy('map_id')
            ->get()
            ->each(function (MapInfluence $influence): void {
                $levels = $influence->map?->influenceLevels ?? collect();
                $influence->setRelation('currentLevel', $levels
                    ->where('is_active', true)
                    ->where('required_influence', '<=', $influence->influence)
                    ->sortByDesc('required_influence')
                    ->first());
                $influence->setRelation('nextLevel', $levels
                    ->where('is_active', true)
                    ->where('required_influence', '>', $influence->influence)
                    ->sortBy('required_influence')
                    ->first());
            });
    }
}
