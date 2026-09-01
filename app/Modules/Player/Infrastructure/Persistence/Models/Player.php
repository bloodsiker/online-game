<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Quest\Domain\Enums\QuestPlayerStatus;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\User\Infrastructure\Persistence\Models\User;
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
 * @property Carbon|null $last_regen_at
 * @property int|null $regen_hp_start
 * @property int|null $regen_mp_start
 * @property float $experience_multiplier
 * @property-read User $user
 * @property-read Race $race
 * @property-read PlayerEquipment $playerEquip
 * @property-read Collection|PlayerSkill[] $skills
 * @property-read Collection|MagicSkill[] $magicSkills
 * @property-read Collection|QuestPlayer[] $quests
 */
class Player extends Model
{
    use HasFactory;

    public const REGEN_INTERVAL = 5;

    public const FULL_REGEN_TIME = 900;

    private bool $savingRegenerationProgress = false;

    protected $casts = [
        'experience_multiplier' => 'float',
        'last_regen_at' => 'datetime',
        'regen_hp_start' => 'integer',
        'regen_mp_start' => 'integer',
    ];

    protected $attributes = [
        'experience_multiplier' => 1.0,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $player): void {
            if ($player->savingRegenerationProgress
                || ! $player->hasRegenerationBaselineColumns()
                || ! $player->isDirty(['hp_now', 'mp_now', 'hp_max', 'mp_max'])) {
                return;
            }

            $player->restartRegenerationBaseline();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class, 'race_id');
    }

    public function playerEquip(): HasOne
    {
        return $this->hasOne(PlayerEquipment::class, 'player_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(PlayerSkill::class, 'player_id')->with('skill');
    }

    public function magicSkills()
    {
        return $this->belongsToMany(MagicSkill::class, 'player_magic_skills')->with('skillEffects')
            ->withPivot(['cooldown_end_at', 'is_equipped', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function recipes()
    {
        return $this->belongsToMany(ShareRecipe::class, 'player_recipes')->withTimestamps();
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

    public function reputations(): HasMany
    {
        return $this->hasMany(PlayerReputation::class, 'player_id');
    }

    public function questsInProgress(): HasMany
    {
        return $this->hasMany(QuestPlayer::class, 'player_id')
            ->where(['status' => QuestPlayerStatus::IN_PROGRESS])
            ->with('quest');
    }

    public function getLeftHandMinDmg(): int
    {
        return $this->min_dmg;
    }

    public function getLeftHandMaxDmg(): int
    {
        return $this->max_dmg;
    }

    public function getRightHandMinDmg(): int
    {
        return $this->min_dmg;
    }

    public function getRightHandMaxDmg(): int
    {
        return $this->max_dmg;
    }

    public function getHpMax(): int
    {
        return $this->hp_max;
    }

    public function getMpMax(): int
    {
        return $this->mp_max;
    }

    public function getStrength()
    {
        return floor($this->strength);
    }

    public function getInt()
    {
        return floor($this->intuition);
    }

    public function getAgility()
    {
        return floor($this->agility);
    }

    public function getMud()
    {
        return floor($this->wisdom);
    }

    public function getIntelligence()
    {
        return floor($this->intelligence);
    }

    public function getEndurance()
    {
        return floor($this->endurance);
    }

    public function getCombatClass(): CombatClass
    {
        $str = (float) $this->strength;
        $agil = (float) $this->agility;
        $int = (float) $this->intuition;

        return match (true) {
            $str >= $agil && $str >= $int => CombatClass::TANK,
            $agil >= $int => CombatClass::DODGE,
            default => CombatClass::CRIT,
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

    public function changeHp(int $amount, ?int $cap = null): void
    {
        $cap = $cap ?? $this->hp_max;
        $this->hp_now = max(0, min($cap, $this->hp_now + $amount));
        $this->save();
    }

    public function changeMp(int $amount, ?int $cap = null): void
    {
        $cap = $cap ?? $this->mp_max;
        $this->mp_now = max(0, min($cap, $this->mp_now + $amount));
        $this->save();
    }

    public function regenerate(?int $hpMax = null, ?int $mpMax = null): void
    {
        $hpMax = $hpMax ?? $this->hp_max;
        $mpMax = $mpMax ?? $this->mp_max;
        $now = Carbon::now();

        if (! $this->hasRegenerationBaselineColumns()) {
            $this->regenerateLegacy($hpMax, $mpMax, $now);

            return;
        }

        $baselineInitialized = false;
        if ($this->last_regen_at === null) {
            $this->last_regen_at = $now;
            $baselineInitialized = true;
        }
        if ($this->regen_hp_start === null) {
            $this->regen_hp_start = (int) $this->hp_now;
            $baselineInitialized = true;
        }
        if ($this->regen_mp_start === null) {
            $this->regen_mp_start = (int) $this->mp_now;
            $baselineInitialized = true;
        }

        $elapsedSeconds = max(0, (int) $this->last_regen_at->diffInSeconds($now, false));
        $regenerationSeconds = min(self::FULL_REGEN_TIME, $elapsedSeconds);
        $hpNow = min($hpMax, (int) $this->regen_hp_start + (int) floor($hpMax * $regenerationSeconds / self::FULL_REGEN_TIME));
        $mpNow = min($mpMax, (int) $this->regen_mp_start + (int) floor($mpMax * $regenerationSeconds / self::FULL_REGEN_TIME));

        if (! $baselineInitialized && $hpNow === (int) $this->hp_now && $mpNow === (int) $this->mp_now) {
            return;
        }

        $this->hp_now = $hpNow;
        $this->mp_now = $mpNow;
        $this->saveRegenerationProgress();
    }

    public function restartRegenerationBaseline(?Carbon $startedAt = null): void
    {
        if (! $this->hasRegenerationBaselineColumns()) {
            return;
        }

        $this->last_regen_at = $startedAt ?? Carbon::now();
        $this->regen_hp_start = (int) $this->hp_now;
        $this->regen_mp_start = (int) $this->mp_now;
    }

    private function hasRegenerationBaselineColumns(): bool
    {
        return array_key_exists('regen_hp_start', $this->attributes)
            && array_key_exists('regen_mp_start', $this->attributes);
    }

    private function saveRegenerationProgress(): void
    {
        $this->savingRegenerationProgress = true;

        try {
            $this->save();
        } finally {
            $this->savingRegenerationProgress = false;
        }
    }

    private function regenerateLegacy(int $hpMax, int $mpMax, Carbon $now): void
    {
        if (! $this->last_regen_at) {
            $this->last_regen_at = $now;
            $this->save();

            return;
        }

        $seconds = (int) $this->last_regen_at->diffInSeconds($now);
        $ticks = intdiv($seconds, self::REGEN_INTERVAL);
        if ($ticks <= 0) {
            return;
        }

        $this->hp_now = min($hpMax, (int) floor($this->hp_now + ($hpMax * $ticks * self::REGEN_INTERVAL / self::FULL_REGEN_TIME)));
        $this->mp_now = min($mpMax, (int) floor($this->mp_now + ($mpMax * $ticks * self::REGEN_INTERVAL / self::FULL_REGEN_TIME)));
        $this->last_regen_at = $this->last_regen_at->addSeconds($ticks * self::REGEN_INTERVAL);
        $this->save();
    }
}
