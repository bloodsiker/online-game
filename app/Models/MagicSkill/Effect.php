<?php

namespace App\Models\MagicSkill;

use App\Models\Player\PlayerEffect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Effect extends Model
{
    use HasFactory;

    protected $table = 'effects';

    protected $fillable = [
        'name', 'slug', 'type', 'description', 'chance', 'duration',
        'is_stackable', 'max_stacks', 'tick_interval', 'value_per_tick',
        'stat_modifiers', 'is_dispellable',
    ];

    protected $casts = [
        'stat_modifiers' => 'array',
        'is_stackable' => 'boolean',
        'is_dispellable' => 'boolean',
    ];

    protected $attributes = [
        'chance' => 0,
        'duration' => 0,
        'is_stackable' => false,
        'max_stacks' => 1,
        'tick_interval' => 1,
        'stat_modifiers' => null,
    ];

    public function magicSkills(): BelongsToMany
    {
        return $this->belongsToMany(MagicSkill::class, 'magic_skill_effects')
            ->withPivot('chance')
            ->withTimestamps();
    }

    public function players(): HasMany
    {
        return $this->hasMany(PlayerEffect::class);
    }

//    public function monsters(): BelongsToMany
//    {
//        return $this->belongsToMany(Monster::class, 'monster_has_effects')
//            ->withPivot('chance')
//            ->withTimestamps();
//    }
}
