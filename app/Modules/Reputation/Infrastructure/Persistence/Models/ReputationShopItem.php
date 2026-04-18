<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReputationShopItem extends Model
{
    protected $table = 'reputation_shop_items';

    protected $fillable = ['reputation_id', 'share_item_id', 'price', 'diamond', 'min_points', 'sort_order'];

    protected $casts = [
        'price' => 'integer',
        'diamond' => 'integer',
        'min_points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class, 'reputation_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ReputationShopItemRequirement::class, 'reputation_shop_item_id');
    }
}
