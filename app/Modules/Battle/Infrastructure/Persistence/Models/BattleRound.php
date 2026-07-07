<?php

declare(strict_types=1);

namespace App\Modules\Battle\Infrastructure\Persistence\Models;

use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection|BattleRoundHit[] $hits
 */
class BattleRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'battle_id',
        'user_id',
        'location_monster_id',
        'round_number',
        'action',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function locationMonster(): BelongsTo
    {
        return $this->belongsTo(MonsterOnLocation::class, 'location_monster_id')->with(['monster']);
    }

    public function hits(): HasMany
    {
        return $this->hasMany(BattleRoundHit::class, 'battle_round_id');
    }
}
