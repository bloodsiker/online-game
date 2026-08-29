<?php

declare(strict_types=1);

namespace App\Modules\Location\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapGatheringResource extends Model
{
    protected $fillable = [
        'map_id',
        'share_item_id',
        'max_active',
        'min_x',
        'max_x',
        'min_y',
        'max_y',
    ];

    protected $casts = [
        'max_active' => 'integer',
        'min_x' => 'integer',
        'max_x' => 'integer',
        'min_y' => 'integer',
        'max_y' => 'integer',
    ];

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(GatheringNode::class);
    }
}
