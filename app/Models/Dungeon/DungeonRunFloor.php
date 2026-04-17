<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Enums\DungeonRunFloorStatus;
use App\Models\Battle\Battle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $run_id
 * @property int $floor_id
 * @property DungeonRunFloorStatus $status
 * @property int|null $battle_id
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $floor_expires_at
 * @property \Carbon\Carbon|null $completed_at
 * @property-read DungeonRun   $run
 * @property-read DungeonFloor $floor
 * @property-read Battle|null  $battle
 */
class DungeonRunFloor extends Model
{
    protected $fillable = [
        'run_id', 'floor_id', 'status', 'battle_id',
        'started_at', 'floor_expires_at', 'completed_at',
    ];

    protected $casts = [
        'status' => DungeonRunFloorStatus::class,
        'started_at' => 'datetime',
        'floor_expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(DungeonRun::class, 'run_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(DungeonFloor::class);
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    public function isExpired(): bool
    {
        return $this->floor_expires_at !== null && now()->gt($this->floor_expires_at);
    }

    public function isActive(): bool
    {
        return $this->status === DungeonRunFloorStatus::ACTIVE;
    }
}
