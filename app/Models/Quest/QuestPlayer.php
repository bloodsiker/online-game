<?php

namespace App\Models\Quest;

use App\Enums\QuestPlayerStatus;
use App\Models\Player\Player;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuestPlayer extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => QuestPlayerStatus::class,
        'reset_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => QuestPlayerStatus::IN_PROGRESS,
        'progress' => 0,
    ];

    protected $with = ['objective', 'quest'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class,'player_id');
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class,'quest_id');
    }

    public function objective(): HasOne
    {
        return $this->hasOne(QuestPlayerObjective::class, 'quest_player_id');
    }
}
