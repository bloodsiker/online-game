<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Enums\ActiveEffectType;
use App\Models\Battle\Battle;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerActiveEffect extends Model
{
    use HasFactory;

    protected $table = 'player_active_effects';

    protected $fillable = [
        'player_id', 'effect_id', 'battle_id', 'type',
        'source_player_id', 'source_magic_skill_id',
        'applied_at', 'expires_at', 'stacks', 'current_value',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'expires_at' => 'datetime',
        'type' => ActiveEffectType::class,
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
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

    public function isStun(): bool
    {
        return $this->type?->isStun() ?? false;
    }

    public function isDoT(): bool
    {
        return $this->type?->isDoT() ?? false;
    }
}
