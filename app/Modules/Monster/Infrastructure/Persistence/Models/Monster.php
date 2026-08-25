<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Domain\Enums\MonsterAttackType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'exp', 'min_money', 'max_money', 'attack_type', 'magic_attack', 'magic_power_coefficient', 'magic_resistance',
        'is_boss', 'respawn_min_minutes', 'respawn_max_minutes',
    ];

    protected $attributes = [
        'is_boss' => false,
    ];

    protected $casts = [
        'is_boss' => 'boolean',
        'attack_type' => MonsterAttackType::class,
        'respawn_at' => 'datetime',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => resolve_storage_image_url($value));
    }

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
            ->withPivot(['chance', 'duration_seconds', 'power_percent', 'trigger_on_hit'])
            ->wherePivot('trigger_on_hit', true)
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

    public function summonPool(): HasMany
    {
        return $this->hasMany(MonsterSummonPool::class, 'monster_id')->with('minionMonster');
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

    /**
     * Планує респаун боса після смерті: now() + рандом(min, max) хвилин.
     * Якщо задано лише min (max порожній) — фіксований респаун.
     */
    public function scheduleRespawn(): void
    {
        $min = $this->respawn_min_minutes ?? 0;
        $max = $this->respawn_max_minutes ?? $min;

        if ($min <= 0 && $max <= 0) {
            return;
        }

        $minutes = $max > $min ? random_int($min, $max) : $min;

        $this->respawn_at = now()->addMinutes($minutes);
        $this->save();
    }

    public function canRespawnNow(): bool
    {
        return $this->respawn_at !== null && $this->respawn_at->isPast();
    }

    public function clearRespawn(): void
    {
        $this->respawn_at = null;
        $this->save();
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

    public function getIntelligence(): int
    {
        return 0;
    }

    public function getMagicResistance(): int
    {
        return max(0, (int) $this->magic_resistance);
    }

    public function getMagicAttack(): int
    {
        return max(0, (int) $this->magic_attack);
    }

    public function usesMagicAttack(): bool
    {
        return ($this->attack_type ?? MonsterAttackType::PHYSICAL)->isMagic();
    }

    /**
     * Класс монстра определяется доминирующей характеристикой.
     * Нормализуем через базовые значения, т.к. у монстров нет первичных стат.
     */
    /**
     * Нормировка вторичных стат монстра к сопоставимым «весам» классов:
     * у монстров нет первичных характеристик, поэтому берём броню/уворот/крит
     * и приводим их к общей шкале (50 брони ≈ 20 уворота ≈ 20 крита).
     */
    private const ARMOR_CLASS_SCALE = 50;

    private const DODGE_CLASS_SCALE = 20;

    private const CRIT_CLASS_SCALE = 20;

    public function getCombatClass(): CombatClass
    {
        $armorScore = $this->armor / self::ARMOR_CLASS_SCALE;
        $dodgeScore = $this->dodge / self::DODGE_CLASS_SCALE;
        $critScore = $this->critical / self::CRIT_CLASS_SCALE;

        return match (true) {
            $armorScore >= $dodgeScore && $armorScore >= $critScore => CombatClass::TANK,
            $dodgeScore >= $critScore => CombatClass::DODGE,
            default => CombatClass::CRIT,
        };
    }

    public function getClassShare(CombatClass $class): float
    {
        $armorScore = $this->armor / self::ARMOR_CLASS_SCALE;
        $dodgeScore = $this->dodge / self::DODGE_CLASS_SCALE;
        $critScore = $this->critical / self::CRIT_CLASS_SCALE;
        $total = max(0.001, $armorScore + $dodgeScore + $critScore);

        return match ($class) {
            CombatClass::TANK => $armorScore / $total,
            CombatClass::DODGE => $dodgeScore / $total,
            CombatClass::CRIT => $critScore / $total,
        };
    }

    public function getCritDamage(): int
    {
        return 150;
    }

    public function getLevel(): int
    {
        return max(1, (int) $this->lvl);
    }

    // Блок щитом — пока только у игроков; мобы не блокируют
    public function getBlockChance(): int
    {
        return 0;
    }

    public function getBlockFlat(): int
    {
        return 0;
    }

    public function getBlockPercent(): int
    {
        return 0;
    }
}
