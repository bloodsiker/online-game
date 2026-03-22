<?php

namespace App\Models\Quest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestClanObjective extends Model
{
    protected $table = 'quest_clan_objectives';

    protected $fillable = ['quest_clan_progress_id', 'quest_objective_id', 'amount'];

    protected $attributes = [
        'amount' => 0,
    ];

    public function clanProgress(): BelongsTo
    {
        return $this->belongsTo(QuestClanProgress::class, 'quest_clan_progress_id');
    }

    public function questObjective(): BelongsTo
    {
        return $this->belongsTo(QuestObjective::class, 'quest_objective_id');
    }
}