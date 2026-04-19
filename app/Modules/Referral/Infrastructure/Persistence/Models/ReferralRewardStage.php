<?php

declare(strict_types=1);

namespace App\Modules\Referral\Infrastructure\Persistence\Models;

use App\Modules\Referral\Domain\Enums\ReferralRewardType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $level_threshold
 * @property ReferralRewardType $reward_type
 * @property int|null $reward_item_id
 * @property int $reward_value
 * @property int $sort
 * @property bool $is_active
 * @property-read ShareItem|null $rewardItem
 */
class ReferralRewardStage extends Model
{
    protected $fillable = ['level_threshold', 'reward_type', 'reward_item_id', 'reward_value', 'sort', 'is_active'];

    protected $casts = [
        'reward_type' => ReferralRewardType::class,
        'is_active' => 'boolean',
    ];

    public function rewardItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'reward_item_id');
    }

    public function description(): string
    {
        return match ($this->reward_type) {
            ReferralRewardType::GOLD => "{$this->reward_value} золота",
            ReferralRewardType::DIAMOND => "{$this->reward_value} алмазов",
            ReferralRewardType::ITEM => "{$this->reward_value}x ".($this->rewardItem?->name ?? '?'),
        };
    }
}
