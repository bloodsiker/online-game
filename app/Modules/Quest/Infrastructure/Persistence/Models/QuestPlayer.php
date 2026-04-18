<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models;

use App\Enums\QuestPlayerStatus;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestPlayer extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => QuestPlayerStatus::class,
        'reset_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $fillable = ['player_id', 'quest_id', 'status', 'current_stage_id', 'completed_at', 'reset_at'];
    protected $attributes = ['status' => QuestPlayerStatus::IN_PROGRESS];
    protected $with = ['objectives.questObjective', 'quest'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(QuestStage::class, 'current_stage_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(QuestPlayerObjective::class, 'quest_player_id');
    }

    public function currentStageObjectives()
    {
        if ($this->current_stage_id === null) {
            return $this->objectives;
        }

        return $this->objectives->filter(
            fn ($obj) => $obj->questObjective->stage_id === $this->current_stage_id
        );
    }

    public function isCurrentStageComplete(): bool
    {
        $objectives = $this->current_stage_id !== null
            ? $this->objectives->filter(fn ($o) => $o->questObjective->stage_id === $this->current_stage_id)
            : $this->objectives;

        if ($objectives->isEmpty()) {
            return false;
        }

        return $objectives->every(fn ($o) => $o->questObjective->type === 'deliver' ||
            $o->amount >= $o->questObjective->required_amount
        );
    }

    public function isAllObjectivesComplete(): bool
    {
        if ($this->quest->hasStages()) {
            return $this->current_stage_id === null;
        }

        if ($this->objectives->isEmpty()) {
            return false;
        }

        return $this->objectives->every(fn ($o) => $o->questObjective->type === 'deliver' ||
            $o->amount >= $o->questObjective->required_amount
        );
    }
}
