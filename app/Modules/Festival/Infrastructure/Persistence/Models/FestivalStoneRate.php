<?php

declare(strict_types=1);

namespace App\Modules\Festival\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FestivalStoneRate extends Model
{
    protected $guarded = [];

    protected $casts = ['points' => 'integer'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
