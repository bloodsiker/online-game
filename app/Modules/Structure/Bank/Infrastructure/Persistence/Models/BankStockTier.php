<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStockTier extends Model
{
    protected $fillable = ['stock_id', 'diamond_threshold', 'sort_order'];

    protected $casts = [
        'diamond_threshold' => 'float',
        'sort_order'        => 'integer',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(BankStock::class, 'stock_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BankStockTierItem::class, 'tier_id')->orderBy('sort_order');
    }
}