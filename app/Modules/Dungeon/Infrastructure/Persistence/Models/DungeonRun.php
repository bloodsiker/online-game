<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\Dungeon\Domain\Enums\DungeonRunStatus;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DungeonRun extends Model
{
    protected $fillable = [
        'dungeon_id', 'leader_user_id', 'current_stage_id', 'status', 'started_at',
        'expires_at', 'stage_started_at', 'stage_expires_at', 'finished_at', 'failure_reason',
    ];

    protected $casts = [
        'status' => DungeonRunStatus::class,
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'stage_started_at' => 'datetime',
        'stage_expires_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(DungeonStage::class, 'current_stage_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(DungeonRunParticipant::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(DungeonSession::class);
    }
}
