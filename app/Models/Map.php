<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;

class Map extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'folder', 'slug'];

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
}
