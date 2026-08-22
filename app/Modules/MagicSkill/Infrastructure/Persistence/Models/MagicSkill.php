<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Infrastructure\Persistence\Models;

use App\Models\Skill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEffect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MagicSkill extends Model
{
    use HasFactory;

    protected $table = 'magic_skills';

    protected $fillable = [
        'name', 'slug', 'description', 'level', 'skill_id', 'type', 'mana_cost',
        'min_damage', 'max_damage', 'base_healing', 'cooldown', 'target_type',
        'is_passive', 'effects',
    ];

    protected $casts = [
        'effects' => 'array',
        'is_passive' => 'boolean',
    ];

    protected $attributes = [
        'level' => 1,
        'base_healing' => 0,
        'is_passive' => false,
    ];

    public function skillEffects(): BelongsToMany
    {
        return $this->belongsToMany(Effect::class, 'magic_skill_effects')
            ->withPivot('chance');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_magic_skills')
            ->withPivot(['cooldown_end_at', 'is_equipped']);
    }

    public function appliedEffects(): HasMany
    {
        return $this->hasMany(PlayerEffect::class, 'source_magic_skill_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(MagicSkillRequirement::class);
    }

    /** Навык владения магией, качающийся за успешное применение этого заклинания (см. MagicAttackStrategy) */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function isAttackSkill(): bool
    {
        return $this->type === 'attack' && ! $this->is_passive;
    }

    public function isBuffSkill(): bool
    {
        return in_array($this->type, ['buff', 'heal'], true) && ! $this->is_passive;
    }
}
