<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStockTierItem extends Model
{
    protected $fillable = ['tier_id', 'share_item_id', 'count', 'sort_order'];

    protected $casts = [
        'count' => 'integer',
        'sort_order' => 'integer',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(BankStockTier::class, 'tier_id');
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
