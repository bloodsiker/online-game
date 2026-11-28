<?php

declare(strict_types=1);

namespace App\Modules\Festival\Infrastructure\Persistence\Models;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FestivalSettings extends Model
{
    protected $guarded = [];

    protected $casts = [
        'collection_days' => 'integer',
        'rewards_days' => 'integer',
        'is_active' => 'boolean',
        'launch_at' => 'datetime',
    ];

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    public function coin(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'coin_share_item_id');
    }
}
