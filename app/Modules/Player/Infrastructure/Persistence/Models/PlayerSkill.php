<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSkill extends Model
{
    use HasFactory;

    protected $table = 'player_skills';

    protected $fillable = ['player_id', 'skill_id', 'lvl', 'exp', 'exp_up', 'exp_diff'];

    protected $attributes = [
        'lvl' => 1,
        'exp' => 0,
        'exp_up' => 1000,
        'exp_diff' => 1000,
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
