<?php

namespace App\Models\Monster;

use App\Models\MagicSkill\Effect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterActiveEffect extends Model
{
    use HasFactory;

    protected $table = 'monaster_active_effects';

    protected $fillable = [
        'monster_id', 'effect_id', 'applied_at', 'expires_at', 'stacks', 'current_value',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    public function effect(): BelongsTo
    {
        return $this->belongsTo(Effect::class);
    }

    // Удобные методы
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPermanent(): bool
    {
        return is_null($this->expires_at);
    }

    public function canStack(): bool
    {
        return $this->effect->is_stackable && $this->stacks < $this->effect->max_stacks;
    }

    public function timeRemaining(): ?int
    {
        if (!$this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }

        return now()->diffInSeconds($this->expires_at);
    }
}
