<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Item\Domain\Enums\InstantRewardType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareItemInstantReward extends Model
{
    protected $fillable = ['share_item_id', 'reward_type'];

    protected $casts = [
        'reward_type' => InstantRewardType::class,
    ];

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }
}
