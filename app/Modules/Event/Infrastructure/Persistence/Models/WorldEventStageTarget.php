<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Models;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WorldEventStageTarget extends Model
{
    protected $fillable = [
        'world_event_stage_id', 'share_item_id', 'monster_id', 'position',
        'spawn_weight', 'max_active', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorldEventStage::class, 'world_event_stage_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }
}
