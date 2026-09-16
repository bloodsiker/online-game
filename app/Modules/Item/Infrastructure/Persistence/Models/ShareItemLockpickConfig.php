<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareItemLockpickConfig extends Model
{
    protected $fillable = [
        'share_item_id',
        'tier',
        'speed_bonus_percent',
        'failure_preserve_chance_percent',
        'trap_avoid_chance_percent',
    ];

    protected $casts = [
        'tier' => 'integer',
        'speed_bonus_percent' => 'integer',
        'failure_preserve_chance_percent' => 'integer',
        'trap_avoid_chance_percent' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
