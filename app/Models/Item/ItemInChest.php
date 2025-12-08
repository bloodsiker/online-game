<?php

namespace App\Models\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemInChest extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'item_in_chest';

    protected $attributes = [
        'count' => 1,
    ];

    public function chest(): BelongsTo
    {
        return $this->belongsTo(Item::class,'chest_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class,  'item_id')->with(['itemInfo']);
    }
}
