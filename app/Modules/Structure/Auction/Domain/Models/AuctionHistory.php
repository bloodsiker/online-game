<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Domain\Models;

use App\Models\Item\Item;
use App\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionHistory extends Model
{
    use HasFactory;

    protected $table = 'auction_histories';

    protected $fillable = ['buy_user_id', 'sell_user_id', 'structure_id', 'item_id', 'count', 'price'];

    public function buyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buy_user_id');
    }

    public function sellUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sell_user_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
