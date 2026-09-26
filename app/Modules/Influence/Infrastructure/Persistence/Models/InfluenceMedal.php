<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfluenceMedal extends Model
{
    protected $fillable = ['map_id', 'name', 'description', 'icon', 'rating_points'];

    protected $casts = ['rating_points' => 'integer'];

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(InfluenceMedalStat::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(MapInfluenceLevel::class);
    }

    public function iconUrl(): ?string
    {
        if (! $this->icon) {
            return null;
        }

        return str_starts_with($this->icon, '/') || str_starts_with($this->icon, 'http')
            ? $this->icon
            : resolve_storage_image_url($this->icon);
    }
}
