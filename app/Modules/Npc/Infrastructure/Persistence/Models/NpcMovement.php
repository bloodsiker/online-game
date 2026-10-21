<?php

declare(strict_types=1);

namespace App\Modules\Npc\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NpcMovement extends Model
{
    public const MODE_MAP = 'map';

    public const MODE_LOCATIONS = 'locations';

    protected $fillable = ['npc_id', 'enabled', 'mode', 'map_id', 'location_ids', 'interval_minutes', 'next_move_at'];

    protected $casts = [
        'enabled' => 'boolean',
        'location_ids' => 'array',
        'interval_minutes' => 'integer',
        'next_move_at' => 'immutable_datetime',
    ];

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }
}
