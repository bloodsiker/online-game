<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Models;

use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

final class WorldEvent extends Model
{
    protected $fillable = [
        'title', 'description', 'objective_type', 'image', 'map_id', 'influence_map_id', 'share_item_id', 'monster_id', 'influence_name', 'next_start_at',
        'repeat_interval_minutes', 'duration_minutes', 'global_limit', 'player_limit',
        'spawn_limit', 'respawn_seconds', 'item_lifetime_minutes', 'influence_per_item',
        'is_active',
    ];

    protected $casts = [
        'next_start_at' => 'datetime',
        'is_active' => 'boolean',
        'objective_type' => WorldEventObjectiveType::class,
    ];

    protected function image(): Attribute
    {
        return Attribute::make(get: fn (?string $value): ?string => resolve_storage_image_url($value));
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function influenceMap(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'influence_map_id');
    }

    public function influenceMapId(): int
    {
        return (int) ($this->influence_map_id ?: $this->map_id);
    }

    public function scheduleNextStartAfter(Carbon $finishedAt): void
    {
        $this->next_start_at = $this->repeat_interval_minutes === null
            ? null
            : $finishedAt->copy()->addMinutes((int) $this->repeat_interval_minutes);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'world_event_locations');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(WorldEventRun::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(WorldEventStage::class)
            ->where('is_active', true)
            ->orderBy('position');
    }

    public function allStages(): HasMany
    {
        return $this->hasMany(WorldEventStage::class)->orderBy('position');
    }

    public function activeRun(): HasOne
    {
        return $this->hasOne(WorldEventRun::class)->where('status', WorldEventRun::STATUS_ACTIVE)->latestOfMany();
    }
}
