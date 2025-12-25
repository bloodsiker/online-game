<?php

namespace App\Models\Player;

use App\Decorator\Player\PlayerInterface;
use App\Enums\QuestPlayerStatus;
use App\Models\MagicSkill\MagicSkill;
use App\Models\Quest\QuestPlayer;
use App\Models\Race;
use App\Models\User;
use App\Services\Combat\FightHitInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model implements PlayerInterface, FightHitInterface
{
    use HasFactory;

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
            ->withPivot(['cooldown_end_at', 'is_equipped']);
    }

    public function activeMagicSkills()
    {
        return $this->magicSkills()->where('is_passive', false)->wherePivot('is_equipped', true);
    }

    public function quests(): HasMany
    {
        return $this->hasMany(QuestPlayer::class, 'player_id')->with('quest');
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

    public function getStrength() {
        return floor($this->str);
    }

    public function getInt() {
        return floor($this->int);
    }

    public function getAgility() {
        return floor($this->agil);
    }

    public function getMud() {
        return floor($this->mud);
    }

    public function getIntelligence() {
        return floor($this->intel);
    }

    public function getDodge(): int
    {
        return $this->dodge;
    }

    public function getCritical(): int
    {
        return $this->critical;
    }

    public function getFreeStats(): int
    {
        return $this->free_stats;
    }

    public function getPercentExp()
    {
        $expGive = $this->exp - ($this->exp_up - $this->exp_diff);

        return round($expGive * 100 / $this->exp_diff, 2);
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
}
