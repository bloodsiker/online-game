<?php

declare(strict_types=1);

namespace App\Modules\Battle\Infrastructure\Persistence;

use App\Modules\Battle\Domain\Contracts\HomepageBossBattlesReader;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EloquentHomepageBossBattlesReader implements HomepageBossBattlesReader
{
    public function latest(int $limit): Collection
    {
        $boss = static fn (Builder $query): Builder => $query->where('is_boss', true);

        return Battle::query()->whereHas('detailsWithMonsters.locationMonster.monster', $boss)
            ->with([
                'detailsWithMonsters' => static fn (HasMany $query): HasMany => $query
                    ->whereHas('locationMonster.monster', $boss)->select(['id', 'battle_id', 'location_monster_id']),
                'detailsWithMonsters.locationMonster' => static fn (BelongsTo $query): BelongsTo => $query->select(['id', 'monster_id']),
                'detailsWithMonsters.locationMonster.monster' => static fn (BelongsTo $query): BelongsTo => $query->select(['id', 'name', 'image', 'lvl', 'is_boss']),
            ])->orderByDesc('created_at')->orderByDesc('id')->limit($limit)->get(['id', 'status', 'created_at']);
    }
}
