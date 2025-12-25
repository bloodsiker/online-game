<?php

namespace App\Models\Shop;

use App\Models\Share\ShareShopCategory;
use App\Models\Share\ShareStructureCategory;
use App\Models\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopItem extends Model
{
    use HasFactory;

    protected $fillable = ['structure_id', 'share_item_id', 'share_structure_category_id', 'price', 'diamond'];

    public function requirements(): HasMany
    {
        return $this->hasMany(ShopItemRequirement::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShareStructureCategory::class, 'share_structure_category_id');
    }
}
