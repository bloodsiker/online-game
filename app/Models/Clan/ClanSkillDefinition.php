<?php

namespace App\Models\Clan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClanSkillDefinition extends Model
{
    protected $fillable = ['name', 'description', 'icon', 'max_level', 'sort_order'];

    public function levels(): HasMany
    {
        return $this->hasMany(ClanSkillLevel::class)->orderBy('level');
    }

    public function learnedSkills(): HasMany
    {
        return $this->hasMany(ClanLearnedSkill::class);
    }
}