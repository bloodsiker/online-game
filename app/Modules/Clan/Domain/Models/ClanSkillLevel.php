<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

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
        'share_item_id',
        'share_item_count',
        'magic_skill_id',
    ];

    protected $casts = [];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(ClanSkillDefinition::class, 'clan_skill_definition_id');
    }

    public function stoneItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function magicSkill(): BelongsTo
    {
        return $this->belongsTo(MagicSkill::class);
    }
}