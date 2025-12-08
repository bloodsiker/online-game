<?php

namespace App\Models\Item;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Item\Item;

class ItemOnLocation extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'item_on_locations';

    protected $attributes = [
        'count' => 1,
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class,'location_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class,  'item_id')->with(['itemInfo']);
    }
}
