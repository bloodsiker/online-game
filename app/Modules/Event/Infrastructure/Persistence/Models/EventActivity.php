<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Models;

use App\Modules\Event\Domain\Enums\ActivityBonusRewardType;
use App\Modules\Event\Domain\Enums\ActivityPeriod;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventActivity extends Model
{
    use HasFactory;

    protected $table = 'event_activities';

    protected $fillable = [
        'period',
        'title',
        'description',
        'monster_id',
        'required_count',
        'reward_share_item_id',
        'reward_item_amount',
        'bonus_reward_type',
        'bonus_reward_amount',
        'bonus_reward_share_item_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'period' => ActivityPeriod::class,
        'bonus_reward_type' => ActivityBonusRewardType::class,
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'required_count' => 1,
        'reward_item_amount' => 1,
        'sort_order' => 0,
        'is_active' => true,
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class, 'monster_id');
    }

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'reward_share_item_id');
    }

    public function bonusRewardItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'bonus_reward_share_item_id');
    }
}
