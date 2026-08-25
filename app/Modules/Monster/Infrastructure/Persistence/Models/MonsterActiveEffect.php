<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterActiveEffect extends Model
{
    use HasFactory;

    protected $table = 'monster_active_effects';

    protected $fillable = [
        'location_monster_id', 'effect_id', 'battle_id', 'type',
        'applied_at', 'last_tick_at', 'expires_at', 'stacks', 'current_value',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'last_tick_at' => 'datetime',
        'expires_at' => 'datetime',
        'type' => ActiveEffectType::class,
    ];

    public function locationMonster(): BelongsTo
    {
        return $this->belongsTo(MonsterOnLocation::class, 'location_monster_id');
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    public function effect(): BelongsTo
    {
        return $this->belongsTo(Effect::class);
    }

    public function isStun(): bool
    {
        return $this->type?->isStun() ?? false;
    }

    public function isControl(): bool
    {
        return $this->type?->isControl() ?? false;
    }

    public function isDoT(): bool
    {
        return $this->type?->isDoT() ?? false;
    }
}
