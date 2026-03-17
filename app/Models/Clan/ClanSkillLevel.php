<?php

namespace App\Models\Clan;

use App\Enums\ClanSkillEffectType;
use App\Models\MagicSkill\MagicSkill;
use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanSkillLevel extends Model
{
    protected $fillable = [
        'clan_skill_definition_id',
        'level',
        'required_clan_level',
        'required_bonus_points',
        'stone_share_item_id',
        'effect_type',
        'effect_value',
        'magic_skill_id',
    ];

    protected $casts = [
        'effect_type' => ClanSkillEffectType::class,
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(ClanSkillDefinition::class, 'clan_skill_definition_id');
    }

    public function stoneItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'stone_share_item_id');
    }

    public function magicSkill(): BelongsTo
    {
        return $this->belongsTo(MagicSkill::class);
    }
}