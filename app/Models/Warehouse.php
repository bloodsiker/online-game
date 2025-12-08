<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Item\Item;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'structure_id', 'item_id'];

//    protected $with = ['item'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
