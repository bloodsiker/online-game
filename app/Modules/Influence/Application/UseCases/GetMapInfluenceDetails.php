<?php

declare(strict_types=1);

namespace App\Modules\Influence\Application\UseCases;

use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluence;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevel;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Support\Collection;

final class GetMapInfluenceDetails
{
    /**
     * @return array{
     *     map: Map,
     *     influence: int,
     *     levels: Collection<int, MapInfluenceLevel>,
     *     currentLevel: MapInfluenceLevel|null,
     *     nextLevel: MapInfluenceLevel|null,
     *     progressPercent: int
     * }
     */
    public function execute(int $userId, Map $map): array
    {
        $map->load(['influenceLevels' => fn ($query) => $query
            ->where('is_active', true)
            ->with(['medal.stats', 'bonuses', 'rewards.item'])]);

        $influence = (int) MapInfluence::query()
            ->where('user_id', $userId)
            ->where('map_id', $map->id)
            ->value('influence');
        /** @var Collection<int, MapInfluenceLevel> $levels */
        $levels = $map->influenceLevels;
        $currentLevel = $levels
            ->where('required_influence', '<=', $influence)
            ->sortByDesc('required_influence')
            ->first();
        $nextLevel = $levels
            ->where('required_influence', '>', $influence)
            ->sortBy('required_influence')
            ->first();
        $start = (int) ($currentLevel?->required_influence ?? 0);
        $end = (int) ($nextLevel?->required_influence ?? $influence);
        $range = max(0, $end - $start);
        $progress = max(0, $influence - $start);

        return [
            'map' => $map,
            'influence' => $influence,
            'levels' => $levels,
            'currentLevel' => $currentLevel,
            'nextLevel' => $nextLevel,
            'progressPercent' => $levels->isEmpty()
                ? 0
                : ($nextLevel !== null && $range > 0
                    ? min(100, (int) round($progress * 100 / $range))
                    : 100),
        ];
    }
}
