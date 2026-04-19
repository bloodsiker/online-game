<?php

declare(strict_types=1);

namespace App\Modules\Battle\Infrastructure\Persistence\Models;

use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $location_id
 * @property int $rounds
 * @property array $boss_metadata
 * @property BattleStatus $status
 * @property-read Location $location
 * @property-read Collection|BattleDetail[] $details
 * @property-read Collection|BattleDetail[] $detailsWithUsers
 * @property-read Collection|BattleDetail[] $detailsWithMonsters
 */
class Battle extends Model
{
    use HasFactory;

    protected $fillable = ['location_id', 'status', 'rounds', 'boss_metadata'];

    protected $casts = [
        'boss_metadata' => 'array',
        'status' => BattleStatus::class,
    ];

    protected $attributes = [
        'status' => BattleStatus::ACTIVE,
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
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
}
