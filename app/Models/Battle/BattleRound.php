<?php

namespace App\Models\Battle;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Monster\MonsterOnLocation;

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
        return $this->belongsTo(User::class,'user_id');
    }

    public function locationMonster(): BelongsTo
    {
        return $this->belongsTo(MonsterOnLocation::class,'location_monster_id')->with(['monster']);
    }
}
