<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopItem extends Model
{
    use HasFactory;

    protected $fillable = ['structure_id', 'share_item_id', 'share_structure_category_id', 'price', 'diamond', 'sort_order'];

    protected $attributes = [
        'price' => 0,
        'diamond' => 0,
        'sort_order' => 0,
    ];

    protected $casts = [
        'price' => 'integer',
        'diamond' => 'integer',
        'sort_order' => 'integer',
    ];

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
