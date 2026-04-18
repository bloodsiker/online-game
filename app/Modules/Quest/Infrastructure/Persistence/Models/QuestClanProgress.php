<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models;

use App\Enums\QuestPlayerStatus;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestClanProgress extends Model
{
    protected $table = 'quest_clan_progress';

    protected $casts = [
        'status' => QuestPlayerStatus::class,
        'reset_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $fillable = ['quest_id', 'clan_id', 'user_id', 'status', 'current_stage_id', 'completed_at', 'reset_at'];
    protected $attributes = ['status' => QuestPlayerStatus::IN_PROGRESS];
    protected $with = ['objectives.questObjective', 'quest'];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(QuestStage::class, 'current_stage_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(QuestClanObjective::class, 'quest_clan_progress_id');
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
