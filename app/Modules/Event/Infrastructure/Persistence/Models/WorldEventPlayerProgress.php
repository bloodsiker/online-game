<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorldEventPlayerProgress extends Model
{
    protected $table = 'world_event_player_progress';

    protected $fillable = ['world_event_run_id', 'world_event_stage_id', 'user_id', 'collected_count', 'influence_earned'];

    public function run(): BelongsTo
    {
        return $this->belongsTo(WorldEventRun::class, 'world_event_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorldEventStage::class, 'world_event_stage_id');
    }
}
