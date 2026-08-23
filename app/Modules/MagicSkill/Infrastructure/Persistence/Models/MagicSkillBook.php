<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicSkillBook extends Model
{
    protected $fillable = ['share_item_id', 'magic_skill_id'];

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }

    public function magicSkill(): BelongsTo
    {
        return $this->belongsTo(MagicSkill::class);
    }
}
