<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Player\Infrastructure\Persistence\Models\PlayerItemUseLimitState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareItemUseLimit extends Model
{
    protected $fillable = [
        'share_item_id',
        'max_uses',
        'period_seconds',
    ];

    protected $casts = [
        'max_uses' => 'integer',
        'period_seconds' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function playerStates(): HasMany
    {
        return $this->hasMany(PlayerItemUseLimitState::class, 'share_item_use_limit_id');
    }
}
