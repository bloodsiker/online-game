<?php

declare(strict_types=1);

namespace App\Models\Player;

use App\Enums\ItemEffectType;
use App\Enums\ItemEffectValueType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerItemBuff extends Model
{
    protected $fillable = [
        'player_id',
        'effect_type',
        'value',
        'value_type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'effect_type' => ItemEffectType::class,
        'value_type'  => ItemEffectValueType::class,
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function timeRemaining(): int
    {
        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }
}