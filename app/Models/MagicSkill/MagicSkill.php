<?php

namespace App\Models\MagicSkill;

use App\Models\Player\Player;
use App\Models\Player\PlayerEffect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $level
 * @property string $type
 * @property int $mana_cost
 * @property int $min_damage
 * @property int $max_damage
 * @property int $base_healing
 * @property int $cooldown
 * @property string $target_type
 * @property bool $is_passive
 * @property array $effects
 */
class MagicSkill extends Model
{
    use HasFactory;

    protected $table = 'magic_skills';

    protected $fillable = [
        'name', 'slug', 'description', 'level', 'type', 'mana_cost',
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

    public function isAttackSkill(): bool
    {
        return $this->type === 'attack' && !$this->is_passive;
    }
}
