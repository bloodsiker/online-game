<?php

namespace App\Models\Player;

use App\Models\MagicSkill\MagicSkill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerMagicSkill extends Model
{
    use HasFactory;

    protected $table = 'player_magic_skills';

    public $timestamps = false;

    protected $fillable = [
        'player_id', 'magic_skill_id', 'cooldown_end_at', 'is_equipped',
    ];

    protected $casts = [
        'cooldown_end_at' => 'datetime',
        'is_equipped' => 'boolean',
    ];

    protected $attributes = [
        'is_equipped' => 0,
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function magicSkill(): BelongsTo
    {
        return $this->belongsTo(MagicSkill::class, 'magic_skill_id');
    }

    // Удобные методы
    public function isOnCooldown(): bool
    {
        return $this->cooldown_end_at && $this->cooldown_end_at->isFuture();
    }

    public function cooldownRemaining(): ?int
    {
        if (!$this->cooldown_end_at || $this->cooldown_end_at->isPast()) {
            return 0;
        }

        return now()->diffInSeconds($this->cooldown_end_at);
    }
}
