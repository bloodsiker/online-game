<?php

declare(strict_types=1);

namespace App\Models\Reputation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReputationTier extends Model
{
    protected $table = 'reputation_tiers';

    protected $fillable = ['reputation_id', 'min_points', 'max_points', 'medal_name', 'medal_icon'];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer',
    ];

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class, 'reputation_id');
    }

    public function quests(): HasMany
    {
        return $this->hasMany(ReputationTierQuest::class, 'tier_id');
    }
}
