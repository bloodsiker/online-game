<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReputationShopCart extends Model
{
    protected $table = 'reputation_shop_carts';

    protected $fillable = ['user_id', 'reputation_shop_item_id', 'quantity'];

    protected $attributes = ['quantity' => 1];

    protected $casts = ['quantity' => 'integer'];

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ReputationShopItem::class, 'reputation_shop_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
