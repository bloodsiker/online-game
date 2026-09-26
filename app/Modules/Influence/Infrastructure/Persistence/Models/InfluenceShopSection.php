<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfluenceShopSection extends Model
{
    protected $fillable = ['structure_id', 'map_id', 'name', 'icon', 'required_influence', 'sort_order', 'is_active'];

    protected $casts = ['required_influence' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean'];

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InfluenceShopItem::class)->orderBy('sort_order');
    }
}
