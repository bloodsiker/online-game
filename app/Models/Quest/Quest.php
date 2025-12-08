<?php

namespace App\Models\Quest;

use App\Enums\QuestType;
use App\Models\Npc;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quest extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => QuestType::class,
        'is_active' => 'boolean',
        'is_finish' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => 1,
        'is_finish' => 0,
    ];

    protected $with = ['objective'];

    public QuestType $type;

    public function startNpc(): BelongsTo
    {
        return $this->belongsTo(Npc::class,'start_npc_id');
    }

    public function completeNpc(): BelongsTo
    {
        return $this->belongsTo(Npc::class,'complete_npc_id');
    }

    public function parentQuest(): BelongsTo
    {
        return $this->belongsTo(Quest::class,'parent_quest_id');
    }

    public function afterQuest(): BelongsTo
    {
        return $this->belongsTo(Quest::class,'after_quest_id');
    }

    public function objective(): HasOne
    {
        return $this->hasOne(QuestObjective::class, 'quest_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(QuestReward::class, 'quest_id');
    }

    public function getTypeLabel(): string
    {
        return $this->type->label();
    }

    public function isRepeatable(): bool
    {
        return $this->type->isRepeatable();
    }

    public function isOneTime(): bool
    {
        return $this->type->isOneTime();
    }

    public function isMain(): bool
    {
        return $this->type->isMain();
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', 1);
    }
}
