<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $item_id
 * @property int $location_id
 * @property int $count
 * @property Carbon|null $expires_at
 */
class ItemOnLocation extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'item_on_locations';

    protected $attributes = [
        'count' => 1,
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id')->with(['itemInfo']);
    }
}
