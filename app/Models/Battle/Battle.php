<?php

namespace App\Models\Battle;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Battle extends Model
{
    use HasFactory;

    public const STATUS_FINISH = 0;
    public const STATUS_ACTIVE = 1;

    protected $fillable = ['location_id', 'status', 'rounds'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class,'location_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BattleDetail::class, 'battle_id')->with(['user', 'locationMonster']);
    }

    public function detailsWithUsers(): HasMany
    {
        return $this->hasMany(BattleDetail::class, 'battle_id')->with(['user'])->whereNotNull('user_id');
    }

    public function detailsWithMonsters(): HasMany
    {
        return $this->hasMany(BattleDetail::class, 'battle_id')->with(['locationMonster'])->whereNotNull('location_monster_id');
    }

    public function isFinish(): bool
    {
        return $this->status === self::STATUS_FINISH;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
