<?php

declare(strict_types=1);

namespace App\Modules\Location\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatheringNode extends Model
{
    protected $fillable = [
        'map_gathering_resource_id',
        'x_percent',
        'y_percent',
        'respawn_at',
    ];

    protected $casts = [
        'x_percent' => 'float',
        'y_percent' => 'float',
        'respawn_at' => 'datetime',
    ];

    public function mapResource(): BelongsTo
    {
        return $this->belongsTo(MapGatheringResource::class, 'map_gathering_resource_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(GatheringAttempt::class);
    }
}
