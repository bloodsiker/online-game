<?php

declare(strict_types=1);

namespace App\Modules\Commerce\Infrastructure\Persistence\Models;

use App\Modules\Commerce\Domain\Enums\PurchaseSourceType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPurchaseLog extends Model
{
    protected $fillable = [
        'purchase_uuid', 'user_id', 'user_name', 'structure_id', 'structure_name',
        'source_type', 'source_id', 'share_item_id', 'item_name', 'quantity',
        'unit_price', 'unit_diamond', 'total_price', 'total_diamond',
        'money_balance_after', 'diamond_balance_after', 'requirements', 'metadata',
    ];

    protected $casts = [
        'source_type' => PurchaseSourceType::class,
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'unit_diamond' => 'integer',
        'total_price' => 'integer',
        'total_diamond' => 'integer',
        'money_balance_after' => 'integer',
        'diamond_balance_after' => 'integer',
        'requirements' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }
}
