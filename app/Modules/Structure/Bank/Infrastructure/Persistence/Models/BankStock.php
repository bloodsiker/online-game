<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStock extends Model
{
    protected $fillable = ['name', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(BankStockTier::class, 'stock_id')->orderBy('sort_order')->orderBy('diamond_threshold');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(BankStockContribution::class, 'stock_id');
    }

    public function isRunning(): bool
    {
        return $this->is_active
            && now()->between($this->starts_at, $this->ends_at);
    }

    public static function current(): ?self
    {
        return self::where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }
}