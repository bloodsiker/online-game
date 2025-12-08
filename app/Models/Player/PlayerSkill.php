<?php

namespace App\Models\Player;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSkill extends Model
{
    use HasFactory;

    public const BASE_EXPERIENCE = 1000;
    public const GROW_FACTOR = 1.5;

    protected $table = 'player_skills';

    protected $attributes = [
        'lvl' => 1,
        'exp' => 0,
        'exp_up' => self::BASE_EXPERIENCE,
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }
}
