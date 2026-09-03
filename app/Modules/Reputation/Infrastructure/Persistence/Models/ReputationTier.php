<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence\Models;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReputationTier extends Model
{
    public const REGULAR_MEDAL_RATING = [
        500 => 10,
        1000 => 20,
        2000 => 50,
        3000 => 100,
    ];

    public const FEAT_MEDAL_RATING = 300;

    protected $table = 'reputation_tiers';

    protected $fillable = [
        'reputation_id',
        'min_points',
        'max_points',
        'medal_name',
        'medal_icon',
        'feat_quest_id',
        'feat_description',
        'feat_medal_name',
        'feat_medal_icon',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer',
    ];

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class, 'reputation_id');
    }

    public function featQuest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'feat_quest_id');
    }

    public function quests(): HasMany
    {
        return $this->hasMany(ReputationTierQuest::class, 'tier_id');
    }

    public function regularMedalRating(): int
    {
        return self::REGULAR_MEDAL_RATING[$this->min_points] ?? 0;
    }

    public function featMedalRating(): int
    {
        return $this->feat_medal_name ? self::FEAT_MEDAL_RATING : 0;
    }
}
