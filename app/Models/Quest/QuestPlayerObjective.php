<?php

namespace App\Models\Quest;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestPlayerObjective extends Model
{
    use HasFactory;

    protected $attributes = [
        'amount' => 0,
    ];

    public function questPlayer(): BelongsTo
    {
        return $this->belongsTo(QuestPlayer::class,'quest_player_id');
    }

    public function questObjective(): BelongsTo
    {
        return $this->belongsTo(QuestObjective::class,'quest_objective_id');
    }
}
