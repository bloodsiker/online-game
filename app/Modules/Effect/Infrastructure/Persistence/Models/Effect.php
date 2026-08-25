<?php

declare(strict_types=1);

namespace App\Modules\Effect\Infrastructure\Persistence\Models;

use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Domain\Enums\EffectDamageScalingType;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEffect;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Effect extends Model
{
    use HasFactory;

    protected $table = 'effects';

    protected $fillable = [
        'name', 'slug', 'type', 'active_type', 'damage_scaling_type', 'description', 'image', 'chance',
        'is_stackable', 'max_stacks', 'tick_interval', 'value_per_tick',
        'stat_modifiers', 'is_dispellable',
    ];

    protected $casts = [
        'stat_modifiers' => 'array',
        'is_stackable' => 'boolean',
        'is_dispellable' => 'boolean',
        'active_type' => ActiveEffectType::class,
        'damage_scaling_type' => EffectDamageScalingType::class,
    ];

    protected $attributes = [
        'chance' => 0,
        'is_stackable' => false,
        'max_stacks' => 1,
        'tick_interval' => 1,
        'stat_modifiers' => null,
    ];

    protected function image(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => resolve_storage_image_url($value));
    }

    public function magicSkills(): BelongsToMany
    {
        return $this->belongsToMany(MagicSkill::class, 'magic_skill_effects')
            ->withPivot(['chance', 'duration_seconds'])
            ->withTimestamps();
    }

    public function monsters(): BelongsToMany
    {
        return $this->belongsToMany(Monster::class, 'monster_effects')
            ->withPivot(['chance', 'duration_seconds', 'power_percent', 'trigger_on_hit'])
            ->wherePivot('trigger_on_hit', true)
            ->withTimestamps();
    }

    /**
     * Older effects encoded their runtime type directly in slug. Keep that
     * fallback so existing spells continue working while new definitions can
     * have independent, descriptive slugs such as monster_bleed.
     */
    public function resolvedActiveType(): ?ActiveEffectType
    {
        return $this->active_type ?? ActiveEffectType::tryFrom((string) $this->slug);
    }

    public function resolvedDamageScalingType(): EffectDamageScalingType
    {
        return $this->damage_scaling_type ?? EffectDamageScalingType::HIT_DAMAGE;
    }

    public function players(): HasMany
    {
        return $this->hasMany(PlayerEffect::class);
    }
}
