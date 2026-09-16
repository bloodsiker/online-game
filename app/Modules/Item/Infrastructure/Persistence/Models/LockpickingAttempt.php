<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockpickingAttempt extends Model
{
    protected $fillable = [
        'player_id',
        'item_id',
        'share_item_id',
        'lockpick_share_item_id',
        'location_id',
        'is_inventory',
        'skill_snapshot',
        'lockpick_tier_snapshot',
        'speed_bonus_snapshot',
        'failure_preserve_chance_snapshot',
        'trap_avoid_chance_snapshot',
        'chance_snapshot',
        'started_at',
        'completes_at',
        'expires_at',
    ];

    protected $casts = [
        'is_inventory' => 'boolean',
        'chance_snapshot' => 'float',
        'started_at' => 'datetime',
        'completes_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
