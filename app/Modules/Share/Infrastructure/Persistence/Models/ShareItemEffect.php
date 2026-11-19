<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $share_item_id
 * @property ItemEffectType $effect_type
 * @property int $value
 * @property ItemEffectValueType $value_type
 * @property int|null $duration_seconds
 * @property int|null $target_share_item_id
 * @property int|null $reputation_id
 * @property-read ShareItem $itemInfo
 */
class ShareItemEffect extends Model
{
    protected $fillable = [
        'share_item_id',
        'effect_type',
        'value',
        'value_type',
        'duration_seconds',
        'target_share_item_id',
        'reputation_id',
    ];

    protected $casts = [
        'effect_type' => ItemEffectType::class,
        'value_type' => ItemEffectValueType::class,
    ];

    public function toValueObject(): ItemEffectValue
    {
        return new ItemEffectValue(
            $this->effect_type,
            $this->value_type,
            $this->value,
            $this->duration_seconds,
            $this->target_share_item_id !== null ? (int) $this->target_share_item_id : null,
            $this->reputation_id !== null ? (int) $this->reputation_id : null,
        );
    }

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class, 'reputation_id');
    }

    public function targetItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'target_share_item_id');
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
