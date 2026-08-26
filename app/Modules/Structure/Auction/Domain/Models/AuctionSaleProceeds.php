<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Domain\Models;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionSaleProceeds extends Model
{
    protected $fillable = ['user_id', 'structure_id', 'auction_history_id', 'amount'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function history(): BelongsTo
    {
        return $this->belongsTo(AuctionHistory::class, 'auction_history_id');
    }
}
