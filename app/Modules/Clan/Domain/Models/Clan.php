<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use App\Enums\QuestPlayerStatus;
use App\Models\Quest\QuestClanProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'news_1', 'news_2', 'news_3', 'icon', 'owner_id', 'lvl', 'warehouse_capacity', 'points', 'treasury'];

    protected $attributes = [
        'lvl' => 1,
    ];

    protected $with = ['owner'];

    public function members(): HasMany
    {
        return $this->hasMany(ClanMember::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(ClanRole::class)->orderBy('id');
    }

    public function learnedSkills(): HasMany
    {
        return $this->hasMany(ClanLearnedSkill::class);
    }

    public function activeQuestProgress(): HasMany
    {
        return $this->hasMany(QuestClanProgress::class)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->with('objectives.questObjective', 'quest', 'user');
    }
}