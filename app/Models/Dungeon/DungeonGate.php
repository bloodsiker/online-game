<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Models\Share\ShareItem;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dungeon_id
 * @property int $from_location_id
 * @property int $to_location_id
 * @property string $unlock_type area_cleared|boss_item
 * @property int|null $boss_share_item_id
 * @property-read Dungeon        $dungeon
 * @property-read Location       $fromLocation
 * @property-read Location       $toLocation
 * @property-read ShareItem|null $bossItem
 */
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
