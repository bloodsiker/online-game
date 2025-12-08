<?php

namespace App\Models\Monster;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
