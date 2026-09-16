<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $experience_reward
 */
class ShareItemLockConfig extends Model
{
    protected $fillable = [
        'share_item_id', 'lock_required_skill', 'lock_duration_seconds', 'experience_reward', 'trap_chance_penalty_percent',
        'trap_effect_id', 'trap_effect_duration_seconds', 'trap_damage_percent',
    ];

    protected $casts = [
        'lock_required_skill' => 'integer',
        'lock_duration_seconds' => 'integer',
        'experience_reward' => 'integer',
        'trap_chance_penalty_percent' => 'integer',
        'trap_effect_duration_seconds' => 'integer',
        'trap_damage_percent' => 'integer',
    ];

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }

    public function trapEffect(): BelongsTo
    {
        return $this->belongsTo(Effect::class, 'trap_effect_id');
    }
}
