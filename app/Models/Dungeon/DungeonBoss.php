<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Models\Monster\Monster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int      $id
 * @property int      $dungeon_id
 * @property int|null $floor_id
 * @property int      $monster_id
 * @property int      $sort_order
 * @property-read Dungeon      $dungeon
 * @property-read DungeonFloor|null $floor
 * @property-read Monster      $monster
 */
class DungeonBoss extends Model
{
    protected $fillable = ['dungeon_id', 'floor_id', 'monster_id', 'sort_order'];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(DungeonFloor::class);
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }
}