<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Domain\Models;

use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionOrder extends Model
{
    protected $fillable = ['user_id', 'structure_id', 'share_item_id', 'count', 'price', 'is_anonymous'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}