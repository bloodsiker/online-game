<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MonsterMovement extends Model
{
    public const MODE_MAP = 'map';

    public const MODE_LOCATIONS = 'locations';

    protected $fillable = ['monster_id', 'enabled', 'mode', 'map_id', 'location_ids', 'interval_minutes', 'next_move_at'];

    protected $casts = [
        'enabled' => 'boolean',
        'location_ids' => 'array',
        'interval_minutes' => 'integer',
        'next_move_at' => 'immutable_datetime',
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }
}
