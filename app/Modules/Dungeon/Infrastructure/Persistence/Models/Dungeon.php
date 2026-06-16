<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Enums\DungeonCooldownType;
use App\Enums\DungeonType;
use App\Models\Map;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dungeon extends Model
{
    protected $fillable = [
        'name', 'description', 'tier', 'type',
        'entry_share_item_id', 'max_players',
        'cooldown_type', 'cooldown_seconds',
        'time_limit_seconds', 'min_level', 'is_active',
        'map_id', 'entry_location_id', 'first_location_id',
        'exit_location_id', 'return_location_id', 'monster_respawn',
        'wave_count',
        'xp_multiplier',
    ];

    protected $casts = [
        'type' => DungeonType::class,
        'cooldown_type' => DungeonCooldownType::class,
        'is_active' => 'boolean',
        'monster_respawn' => 'boolean',
    ];

    public function entryItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'entry_share_item_id');
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function entryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'entry_location_id');
    }

    public function firstLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'first_location_id');
    }

    public function exitLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'exit_location_id');
    }

    public function returnLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'return_location_id');
    }

    public function gates(): HasMany
    {
        return $this->hasMany(DungeonGate::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(DungeonSession::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'dungeon_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(DungeonReward::class);
    }

    public function requiresKey(): bool
    {
        return $this->entry_share_item_id !== null;
    }

    public function isGroup(): bool
    {
        return $this->max_players > 1;
    }

    public function hasGlobalCooldown(): bool
    {
        return $this->cooldown_type === DungeonCooldownType::GLOBAL;
    }

    public function hasDungeonTimer(): bool
    {
        return $this->time_limit_seconds !== null;
    }

    public function isSurvival(): bool
    {
        return $this->type === DungeonType::SURVIVAL;
    }
}
