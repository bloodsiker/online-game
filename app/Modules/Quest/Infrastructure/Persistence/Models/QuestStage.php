<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestStage extends Model
{
    protected $fillable = ['quest_id', 'complete_npc_id', 'order', 'title', 'description'];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }

    public function completeNpc(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'complete_npc_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(QuestObjective::class, 'stage_id');
    }
}
