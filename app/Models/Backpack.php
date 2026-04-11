<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Item\Item;

/**
 * @property int $user_id
 * @property int $item_id
 * @property bool $equipped
 * @property int $count
 */
class Backpack extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'user_id', 'count', 'sort_order'];

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
        return $this->equipped == 1;
    }
}
