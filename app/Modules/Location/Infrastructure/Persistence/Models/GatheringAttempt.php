<?php

declare(strict_types=1);

namespace App\Modules\Location\Infrastructure\Persistence\Models;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatheringAttempt extends Model
{
    public const CLAIM_GRACE_SECONDS = 30;

    protected $fillable = [
        'player_id',
        'gathering_node_id',
        'location_id',
        'completes_at',
        'expires_at',
    ];

    protected $casts = [
        'completes_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(GatheringNode::class, 'gathering_node_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
