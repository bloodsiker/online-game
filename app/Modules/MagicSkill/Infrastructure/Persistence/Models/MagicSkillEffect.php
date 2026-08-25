<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MagicSkillEffect extends Model
{
    use HasFactory;

    protected $table = 'magic_skill_effects';

    public $timestamps = true;

    protected $fillable = ['magic_skill_id', 'effect_id', 'chance', 'duration_seconds'];
}
