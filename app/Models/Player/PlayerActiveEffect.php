<?php

namespace App\Models\Player;

use App\Models\MagicSkill\Effect;
use App\Models\MagicSkill\MagicSkill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerActiveEffect extends Model
{
    use HasFactory;

    protected $table = 'player_active_effects';

    protected $fillable = [
        'player_id', 'effect_id', 'source_player_id',
        'source_magic_skill_id', 'applied_at', 'expires_at',
        'stacks', 'current_value',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
    public function effect(): BelongsTo
    {
        return $this->belongsTo(Effect::class);
    }
    public function sourcePlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'source_player_id');
    }
    public function sourceMagicSkill(): BelongsTo
    {
        return $this->belongsTo(MagicSkill::class, 'source_magic_skill_id');
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
