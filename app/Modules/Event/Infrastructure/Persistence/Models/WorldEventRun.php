<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorldEventRun extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'world_event_id', 'current_stage_id', 'status', 'collected_count', 'starts_at', 'ends_at',
        'next_spawn_at', 'finished_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_spawn_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(WorldEvent::class, 'world_event_id');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(WorldEventPlayerProgress::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(WorldEventStage::class, 'current_stage_id');
    }
}
