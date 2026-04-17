<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Enums\DungeonRunStatus;
use App\Models\Party\Party;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $dungeon_id
 * @property int $leader_user_id
 * @property int|null $party_id
 * @property DungeonRunStatus $status
 * @property int|null $current_floor_id
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $completed_at
 * @property array|null $metadata
 * @property-read Dungeon      $dungeon
 * @property-read User         $leader
 * @property-read Party|null   $party
 * @property-read DungeonFloor|null $currentFloor
 * @property-read \Illuminate\Database\Eloquent\Collection<DungeonRunParticipant> $participants
 * @property-read \Illuminate\Database\Eloquent\Collection<DungeonRunFloor>       $floors
 * @property-read \Illuminate\Database\Eloquent\Collection<DungeonRunLog>         $logs
 */
class DungeonRun extends Model
{
    protected $fillable = [
        'dungeon_id', 'leader_user_id', 'party_id',
        'status', 'current_floor_id',
        'started_at', 'expires_at', 'completed_at', 'metadata',
    ];

    protected $casts = [
        'status' => DungeonRunStatus::class,
        'metadata' => 'array',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function currentFloor(): BelongsTo
    {
        return $this->belongsTo(DungeonRunFloor::class, 'current_floor_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(DungeonRunParticipant::class, 'run_id');
    }

    public function floors(): HasMany
    {
        return $this->hasMany(DungeonRunFloor::class, 'run_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DungeonRunLog::class, 'run_id');
    }

    public function activeRunFloor(): ?DungeonRunFloor
    {
        return $this->floors()->where('status', 'active')->latest()->first();
    }

    public function isActive(): bool
    {
        return $this->status === DungeonRunStatus::ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }

    public function isSolo(): bool
    {
        return $this->party_id === null;
    }
}
