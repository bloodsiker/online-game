<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanSkillLevelItemRequirement extends Model
{
    protected $fillable = ['clan_skill_level_id', 'share_item_id', 'count'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(ClanSkillLevel::class, 'clan_skill_level_id');
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
