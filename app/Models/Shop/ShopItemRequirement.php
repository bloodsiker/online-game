<?php

namespace App\Models\Shop;

use App\Models\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItemRequirement extends Model
{
    use HasFactory;

    protected $fillable = ['share_item_id', 'quantity'];

    public function item()
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
