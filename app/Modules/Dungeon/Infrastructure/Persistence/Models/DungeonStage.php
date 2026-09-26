<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\Dungeon\Domain\Enums\DungeonStageCompletionType;
use App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DungeonStage extends Model
{
    protected $fillable = [
        'dungeon_id', 'number', 'name', 'time_limit_seconds', 'completion_type',
        'spawn_type', 'total_monsters', 'next_stage_id',
    ];

    protected $casts = [
        'completion_type' => DungeonStageCompletionType::class,
        'spawn_type' => DungeonStageSpawnType::class,
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'dungeon_stage_locations')
            ->withPivot(['is_entry', 'position'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function monsters(): HasMany
    {
        return $this->hasMany(DungeonStageMonster::class);
    }

    public function nextStage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_stage_id');
    }

    public function entryLocation(): ?Location
    {
        return $this->locations->first(fn (Location $location): bool => (bool) $location->pivot->is_entry)
            ?? $this->locations->first();
    }
}
