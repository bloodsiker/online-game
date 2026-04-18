<?php

namespace App\Models\Battle;

use App\Enums\BattleDetailStatus;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int $location_monster_id
 * @property BattleDetailStatus $status
 * @property-read User $user
 * @property-read MonsterOnLocation $locationMonster
 */
class BattleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'battle_id', 'user_id', 'location_monster_id',
    ];

    protected $casts = [
        'status' => BattleDetailStatus::class,
    ];

    protected $attributes = [
        'status' => BattleDetailStatus::LIFE,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function locationMonster(): BelongsTo
    {
        return $this->belongsTo(MonsterOnLocation::class, 'location_monster_id')->with(['monster']);
    }
}
