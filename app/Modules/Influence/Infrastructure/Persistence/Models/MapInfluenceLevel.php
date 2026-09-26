<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapInfluenceLevel extends Model
{
    protected $fillable = ['map_id', 'level', 'name', 'required_influence', 'influence_medal_id', 'is_active', 'sort_order'];

    protected $casts = [
        'level' => 'integer',
        'required_influence' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function medal(): BelongsTo
    {
        return $this->belongsTo(InfluenceMedal::class, 'influence_medal_id');
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(MapInfluenceLevelBonus::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(MapInfluenceLevelReward::class);
    }
}
