<?php

namespace App\Models\Monster;

use App\Models\Location\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $location_id
 * @property int $monster_id
 * @property int $hp_max
 * @property int $hp_now
 * @property int $active
 * @property int $is_drop_money
 * @property int $current_phase
 *
 * @property-read Location $location
 * @property-read Monster $monster
 */
class MonsterOnLocation extends Model
{
    use HasFactory;

    protected $table = 'monster_on_locations';

    protected $attributes = [
        'is_drop_money' => 0,
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class,'location_id');
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class,  'monster_id');
    }
}
