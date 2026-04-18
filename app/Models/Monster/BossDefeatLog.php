<?php

namespace App\Models\Monster;

use App\Models\Battle\Battle;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BossDefeatLog extends Model
{
    protected $fillable = [
        'monster_id', 'battle_id', 'player_id',
        'turns_taken', 'mechanics_triggered', 'final_phase',
    ];

    protected $casts = [
        'mechanics_triggered' => 'array',
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }
}
