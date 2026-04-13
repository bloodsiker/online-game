<?php

namespace App\Models\Player;

use App\Enums\CombatClass;
use App\Enums\QuestPlayerStatus;
use App\Models\MagicSkill\MagicSkill;
use App\Models\Quest\QuestPlayer;
use App\Models\Race;
use App\Models\User;
use App\Services\Combat\FightHitInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $hp_now
 * @property int $hp_max
 * @property int $mp_now
 * @property int $mp_max
 *
 * @property-read User $user
 * @property-read Race $race
 * @property-read PlayerEquipment $playerEquip
 * @property-read Collection|PlayerSkill[] $skills
 * @property-read Collection|MagicSkill[] $magicSkills
 * @property-read Collection|QuestPlayer[] $quests
 */
class Player extends Model implements FightHitInterface
{
    use HasFactory;

    const REGEN_INTERVAL = 5; // секунд
    const FULL_REGEN_TIME = 300; // 20 минут в секундах

    protected $casts = [
        'last_regen_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class,  'race_id');
    }

    public function playerEquip(): HasOne
    {
        return $this->hasOne(PlayerEquipment::class, 'player_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(PlayerSkill::class,  'player_id')->with('skill');
    }

    public function magicSkills()
    {
        return $this->belongsToMany(MagicSkill::class, 'player_magic_skills')->with('skillEffects')
            ->withPivot(['cooldown_end_at', 'is_equipped', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function activeMagicSkills()
    {
        return $this->magicSkills()->where('is_passive', false)->wherePivot('is_equipped', true);
    }

    public function quests(): HasMany
    {
        return $this->hasMany(QuestPlayer::class, 'player_id')->with('quest');
    }

    public function hotbarSlots(): HasMany
    {
        return $this->hasMany(PlayerSlot::class, 'player_id')->orderBy('slot_number');
    }

    public function questsInProgress(): HasMany
    {
        return $this->hasMany(QuestPlayer::class, 'player_id')->where(['status' => QuestPlayerStatus::IN_PROGRESS])->with('quest');
    }

    public function getLeftHandMinDmg(): int
    {
        return $this->min_dmg;
    }

    public function getLeftHandMaxDmg(): int
    {
        return $this->max_dmg;
    }

    public function getRightHandMinDmg():int
    {
        return $this->min_dmg;
    }

    public function getRightHandMaxDmg():int
    {
        return $this->max_dmg;
    }

    public function getArmor(): int
    {
        return 0;
    }

    public function getHpMax(): int
    {
        return $this->hp_max;
    }

    public function getMpMax(): int
    {
        return $this->mp_max;
    }

    public function getStrength() {
        return floor($this->strength);
    }

    public function getInt() {
        return floor($this->intuition);
    }

    public function getAgility() {
        return floor($this->agility);
    }

    public function getMud() {
        return floor($this->wisdom);
    }

    public function getIntelligence() {
        return floor($this->intelligence);
    }

    public function getDodge(): int
    {
        return 0; // computed on-the-fly via PlayerStatService
    }

    public function getCritical(): int
    {
        return 0; // computed on-the-fly via PlayerStatService
    }

    public function getCombatClass(): CombatClass
    {
        $str  = (float) $this->strength;
        $agil = (float) $this->agility;
        $int  = (float) $this->intuition;

        return match(true) {
            $str >= $agil && $str >= $int => CombatClass::TANK,
            $agil >= $int                 => CombatClass::DODGE,
            default                       => CombatClass::CRIT,
        };
    }

    public function getClassDominance(): float
    {
        $str   = (float) $this->strength;
        $agil  = (float) $this->agility;
        $int   = (float) $this->intuition;
        $total = max(1.0, $str + $agil + $int);

        return match($this->getCombatClass()) {
            CombatClass::TANK  => $str  / $total,
            CombatClass::DODGE => $agil / $total,
            CombatClass::CRIT  => $int  / $total,
        };
    }

    public function getFreeStats(): int
    {
        return $this->free_stats;
    }

    public function getPercentExp()
    {
        if ($this->exp_diff <= 0) {
            return 0;
        }

        $expGive = $this->exp - ($this->exp_up - $this->exp_diff);

        return min(max(round($expGive * 100 / $this->exp_diff, 2), 0), 100);
    }

    public function getPercentHp()
    {
        return round($this->hp_now * 100 / $this->hp_max);
    }

    public function getPercentMp()
    {
        return $this->mp_max === 0 ? 100 : round($this->mp_now * 100 / $this->mp_max);
    }

    public function getSumStats()
    {
        return $this->getStrength() + $this->getInt() + $this->getAgility() + $this->getMud() + $this->getIntelligence();
    }

    public function hasEquippedMagicSkill(): bool
    {
        return $this->magicSkills()
            ->wherePivot('is_equipped', true)
            ->exists();
    }

    public function regenerate(int $hpMax = null, int $mpMax = null): void
    {
        $hpMax = $hpMax ?? $this->hp_max;
        $mpMax = $mpMax ?? $this->mp_max;

        $now = Carbon::now();

        if (!$this->last_regen_at) {
            $this->last_regen_at = $now;
            $this->save();
            return;
        }

        $seconds = (int) $this->last_regen_at->diffInSeconds($now);
        $ticks = intdiv($seconds, self::REGEN_INTERVAL);

        if ($ticks <= 0) {
            return;
        }

        $totalTicks = self::FULL_REGEN_TIME / self::REGEN_INTERVAL;

        $hpPerTick = ($hpMax / $totalTicks);
        $mpPerTick = ($mpMax / $totalTicks);

        $this->hp_now = min(
            $hpMax,
            (int) floor($this->hp_now + ($hpPerTick * $ticks))
        );

        $this->mp_now = min(
            $mpMax,
            (int) floor($this->mp_now + ($mpPerTick * $ticks))
        );

        $this->last_regen_at = $this->last_regen_at->addSeconds($ticks * self::REGEN_INTERVAL);

        $this->save();
    }

    public function changeHp(int $amount, int $hpMax = null): void
    {
        $cap = $hpMax ?? $this->hp_max;

        $this->hp_now = max(0, min($cap, $this->hp_now + $amount));

        $this->save();
    }

    public function changeMp(int $amount, int $mpMax = null): void
    {
        $cap = $mpMax ?? $this->mp_max;

        $this->mp_now = max(0, min($cap, $this->mp_now + $amount));

        $this->save();
    }
}
