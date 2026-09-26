<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Influence\Domain\Enums\InfluenceRewardType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapInfluenceLevelReward extends Model
{
    protected $fillable = ['map_influence_level_id', 'reward_type', 'share_item_id', 'amount', 'config'];

    protected $casts = ['reward_type' => InfluenceRewardType::class, 'amount' => 'integer', 'config' => 'array'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(MapInfluenceLevel::class, 'map_influence_level_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
