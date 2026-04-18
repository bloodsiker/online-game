<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReputationShopItemRequirement extends Model
{
    protected $table = 'reputation_shop_item_requirements';

    protected $fillable = ['reputation_shop_item_id', 'share_item_id', 'quantity'];

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ReputationShopItem::class, 'reputation_shop_item_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
