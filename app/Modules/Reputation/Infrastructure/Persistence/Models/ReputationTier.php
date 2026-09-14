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
        'favor_percent',
        'feat_favor_percent',
        'favor_duration_seconds',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer',
        'favor_percent' => 'integer',
        'feat_favor_percent' => 'integer',
        'favor_duration_seconds' => 'integer',
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

    public function medalIconUrl(): ?string
    {
        return $this->resolveMedalIconUrl($this->getAttribute('medal_icon'));
    }

    public function featMedalIconUrl(): ?string
    {
        return $this->resolveMedalIconUrl($this->getAttribute('feat_medal_icon'));
    }

    private function resolveMedalIconUrl(?string $icon): ?string
    {
        if ($icon === null || $icon === '') {
            return null;
        }

        if (str_starts_with($icon, 'http://') || str_starts_with($icon, 'https://') || str_starts_with($icon, '/')) {
            return $icon;
        }

        return str_starts_with($icon, 'reputations/medals/')
            ? resolve_storage_image_url($icon)
            : asset($icon);
    }
}
