<?php

namespace App\Models\Clan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanLearnedSkill extends Model
{
    protected $fillable = ['clan_id', 'clan_skill_definition_id', 'current_level'];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(ClanSkillDefinition::class, 'clan_skill_definition_id');
    }

    public function currentLevelData(): BelongsTo
    {
        return $this->belongsTo(ClanSkillLevel::class, 'current_level', 'level')
            ->where('clan_skill_definition_id', $this->clan_skill_definition_id);
    }
}