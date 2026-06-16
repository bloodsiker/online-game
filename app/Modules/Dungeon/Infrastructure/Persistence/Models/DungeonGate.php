<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DungeonGate extends Model
{
    protected $fillable = [
        'dungeon_id', 'from_location_id', 'to_location_id',
        'unlock_type', 'boss_share_item_id',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function bossItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'boss_share_item_id');
    }
}
