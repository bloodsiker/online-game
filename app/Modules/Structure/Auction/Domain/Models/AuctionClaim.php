<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Domain\Models;

use App\Models\Item\Item;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionClaim extends Model
{
    protected $fillable = ['user_id', 'structure_id', 'item_id', 'count'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->with('itemInfo');
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}