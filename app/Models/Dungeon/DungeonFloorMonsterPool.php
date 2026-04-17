<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Models\Monster\Monster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $floor_id
 * @property int $monster_id
 * @property int $weight
 * @property-read DungeonFloor $floor
 * @property-read Monster      $monster
 */
class DungeonFloorMonsterPool extends Model
{
    protected $table = 'dungeon_floor_monster_pool';

    public $timestamps = false;

    protected $fillable = ['floor_id', 'monster_id', 'weight'];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(DungeonFloor::class, 'floor_id');
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }
}
