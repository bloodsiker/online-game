<?php

declare(strict_types=1);

namespace App\Models\Skill;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillLevelRequirement extends Model
{
    public $timestamps = false;

    protected $fillable = ['skill_id', 'exp_required'];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
