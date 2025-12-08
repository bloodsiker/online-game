<?php

namespace app\Models\MagicSkill;

use App\Models\Player\Player;
use App\Models\Player\PlayerEffect;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MagicSkillEffect extends Model
{
    use HasFactory;

    public $table = 'magic_skill_effects';
    public $timestamps = true;

    protected $fillable = ['magic_skill_id', 'effect_id', 'chance'];
}
