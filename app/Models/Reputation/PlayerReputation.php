<?php

declare(strict_types=1);

namespace App\Models\Reputation;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerReputation extends Model
{
    protected $table = 'player_reputations';

    protected $fillable = ['player_id', 'reputation_id', 'points', 'last_completed_at'];

    protected $casts = [
        'points' => 'integer',
        'last_completed_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class, 'reputation_id')->with('tiers');
    }
}
