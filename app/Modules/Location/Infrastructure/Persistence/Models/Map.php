<?php

namespace App\Modules\Location\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Map extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'folder', 'slug', 'has_gathering_field', 'gathering_field_image'];

    protected function casts(): array
    {
        return [
            'has_gathering_field' => 'boolean',
        ];
    }

    protected function gatheringFieldImage(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => $value !== null ? resolve_storage_image_url($value) : null);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Map::class, 'parent_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'map_id');
    }

    public function gatheringResources(): HasMany
    {
        return $this->hasMany(MapGatheringResource::class);
    }
}
