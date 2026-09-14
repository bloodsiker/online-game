<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemUseLimit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerItemUseLimitState extends Model
{
    protected $fillable = [
        'player_id',
        'share_item_use_limit_id',
        'period_started_at',
        'uses_count',
    ];

    protected $casts = [
        'period_started_at' => 'datetime',
        'uses_count' => 'integer',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function useLimit(): BelongsTo
    {
        return $this->belongsTo(ShareItemUseLimit::class, 'share_item_use_limit_id');
    }
}
