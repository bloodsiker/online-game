<?php

namespace App\Models\Monster;

use App\Enums\CombatClass;
use App\Models\Location\Location;
use App\Models\MagicSkill\Effect;
use App\Models\Share\ShareItem;
use App\Services\Combat\FightHitInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monster extends Model implements FightHitInterface
{
    use HasFactory;

    protected $fillable = [
        'lvl', 'name', 'description', 'image', 'hp', 'armor', 'dodge', 'critical', 'min_dmg', 'max_dmg', 'aggression',
        'exp', 'min_money', 'max_money', 'is_boss',
    ];

    protected $attributes = [
        'is_boss' => false,
    ];

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_has_monsters', 'monster_id', 'location_id')->withPivot('aggression');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'monster_has_items', 'monster_id', 'share_item_id')
            ->withPivot('drop_chance', 'min_count', 'max_count');
    }

    public function effects(): BelongsToMany
    {
        return $this->belongsToMany(Effect::class, 'monster_effects')
            ->withPivot('chance')
            ->withTimestamps();
    }

    public function mechanics(): HasMany
    {
        return $this->hasMany(BossMechanic::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(BossPhase::class)->orderBy('phase_number');
    }

    public function getActiveMechanics(): Collection
    {
        return $this->mechanics()
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();
    }

    public function isBoss(): bool
    {
        return $this->is_boss;
    }

    public function getDodge(): int
    {
        return $this->dodge;
    }

    public function getCritical(): int
    {
        return $this->critical;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    /**
     * Класс монстра определяется доминирующей характеристикой.
     * Нормализуем через базовые значения, т.к. у монстров нет первичных стат.
     */
    public function getCombatClass(): CombatClass
    {
        $armorScore = $this->armor    / 50;
        $dodgeScore = $this->dodge    / 20;
        $critScore  = $this->critical / 20;

        return match(true) {
            $armorScore >= $dodgeScore && $armorScore >= $critScore => CombatClass::TANK,
            $dodgeScore >= $critScore                               => CombatClass::DODGE,
            default                                                 => CombatClass::CRIT,
        };
    }

    public function getClassDominance(): float
    {
        $armorScore = $this->armor    / 50;
        $dodgeScore = $this->dodge    / 20;
        $critScore  = $this->critical / 20;
        $total      = max(0.001, $armorScore + $dodgeScore + $critScore);

        return match($this->getCombatClass()) {
            CombatClass::TANK  => $armorScore / $total,
            CombatClass::DODGE => $dodgeScore / $total,
            CombatClass::CRIT  => $critScore  / $total,
        };
    }
}
