<?php

namespace App\Models;

use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exchange extends Model
{
    use HasFactory;

    protected $fillable = ['structure_id', 'from_share_item_id', 'to_share_item_id', 'from_amount', 'to_amount', 'sort_order'];

    //    protected $with = ['item'];

    protected $attributes = [
        'sort_order' => 0,
    ];

    public function fromItem()
    {
        return $this->belongsTo(ShareItem::class, 'from_share_item_id');
    }

    public function toItem()
    {
        return $this->belongsTo(ShareItem::class, 'to_share_item_id');
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
