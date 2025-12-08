<?php

namespace App\Models\Quest;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestObjective extends Model
{
    use HasFactory;

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class,'quest_id');
    }
}
