<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $from_floor_id
 * @property int         $to_floor_id
 * @property string|null $label
 * @property int         $sort_order
 * @property-read DungeonFloor $fromFloor
 * @property-read DungeonFloor $toFloor
 */
class DungeonFloorBranch extends Model
{
    public $timestamps = false;

    protected $fillable = ['from_floor_id', 'to_floor_id', 'label', 'sort_order'];

    public function fromFloor(): BelongsTo
    {
        return $this->belongsTo(DungeonFloor::class, 'from_floor_id');
    }

    public function toFloor(): BelongsTo
    {
        return $this->belongsTo(DungeonFloor::class, 'to_floor_id');
    }
}