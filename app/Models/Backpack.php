<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Item\Item;

class Backpack extends Model
{
    use HasFactory;

    protected $fillable = ['item_id'];

    protected $attributes = [
        'equipped' => 0,
        'count' => 1,
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id')->with(['itemInfo']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isEquipped(): bool
    {
        return $this->equipped === 1;
    }
}
