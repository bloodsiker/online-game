<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfluenceShopItem extends Model
{
    protected $fillable = [
        'influence_shop_section_id', 'share_item_id', 'required_influence',
        'price', 'diamond', 'purchase_limit', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'required_influence' => 'integer', 'price' => 'integer', 'diamond' => 'integer',
        'purchase_limit' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(InfluenceShopSection::class, 'influence_shop_section_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
