<?php

namespace app\Models\MagicSkill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagicSkillEffect extends Model
{
    use HasFactory;

    public $table = 'magic_skill_effects';

    public $timestamps = true;

    protected $fillable = ['magic_skill_id', 'effect_id', 'chance'];
}
