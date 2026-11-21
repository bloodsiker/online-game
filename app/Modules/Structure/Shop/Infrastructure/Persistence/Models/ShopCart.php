<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Infrastructure\Persistence\Models;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopCart extends Model
{
    use HasFactory;

    /** Покупка товара магазина (shop_item_id). */
    public const TYPE_BUY = 'buy';

    /** Продажа предмета из сумки (item_id) в магазин structure_id. */
    public const TYPE_SELL = 'sell';

    protected $fillable = ['user_id', 'type', 'structure_id', 'shop_item_id', 'item_id', 'quantity'];

    protected $attributes = [
        'type' => self::TYPE_BUY,
        'quantity' => 1,
    ];

    /** @param  Builder<self>  $query */
    public function scopeBuying(Builder $query): void
    {
        $query->where('shop_carts.type', self::TYPE_BUY);
    }

    /** @param  Builder<self>  $query */
    public function scopeSelling(Builder $query): void
    {
        $query->where('shop_carts.type', self::TYPE_SELL);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
