<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property int         $dungeon_id
 * @property int         $floor_number
 * @property string|null $label
 * @property bool        $is_first
 * @property bool        $is_boss_floor
 * @property int|null    $time_limit_seconds
 * @property int         $min_monsters
 * @property int         $max_monsters
 * @property int         $wave_count
 * @property array|null  $extra_data
 * @property-read Dungeon $dungeon
 * @property-read \Illuminate\Database\Eloquent\Collection<DungeonFloorBranch>      $nextBranches
 * @property-read \Illuminate\Database\Eloquent\Collection<DungeonFloorMonsterPool> $monsterPool
 * @property-read \Illuminate\Database\Eloquent\Collection<DungeonBoss>             $bosses
 */
class DungeonFloor extends Model
{
    protected $fillable = [
        'dungeon_id', 'floor_number', 'label', 'is_first', 'is_boss_floor',
        'time_limit_seconds', 'min_monsters', 'max_monsters', 'wave_count', 'extra_data',
    ];

    protected $casts = [
        'is_first'     => 'boolean',
        'is_boss_floor' => 'boolean',
        'extra_data'   => 'array',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function nextBranches(): HasMany
    {
        return $this->hasMany(DungeonFloorBranch::class, 'from_floor_id')->orderBy('sort_order');
    }

    public function monsterPool(): HasMany
    {
        return $this->hasMany(DungeonFloorMonsterPool::class, 'floor_id');
    }

    public function bosses(): HasMany
    {
        return $this->hasMany(DungeonBoss::class, 'floor_id');
    }

    public function hasFloorTimer(): bool
    {
        return $this->time_limit_seconds !== null;
    }

    public function hasBranching(): bool
    {
        return $this->nextBranches()->exists();
    }
}