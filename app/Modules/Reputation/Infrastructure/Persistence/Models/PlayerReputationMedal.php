<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence\Models;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerReputationMedal extends Model
{
    protected $fillable = ['player_id', 'reputation_id', 'tier_id', 'is_feat', 'earned_at'];

    protected $casts = [
        'is_feat' => 'boolean',
        'earned_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(ReputationTier::class, 'tier_id');
    }
}
