<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Models;

use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorldEventStage extends Model
{
    protected $fillable = [
        'world_event_id', 'position', 'title', 'objective_type', 'share_item_id', 'monster_id',
        'global_limit', 'player_limit', 'spawn_limit', 'respawn_seconds',
        'item_lifetime_minutes', 'influence_per_item', 'is_active',
    ];

    protected $casts = [
        'objective_type' => WorldEventObjectiveType::class,
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(WorldEvent::class, 'world_event_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(WorldEventPlayerProgress::class, 'world_event_stage_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(WorldEventStageTarget::class)
            ->where('is_active', true)
            ->orderBy('position');
    }

    public function allTargets(): HasMany
    {
        return $this->hasMany(WorldEventStageTarget::class)->orderBy('position');
    }
}
