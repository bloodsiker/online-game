<?php

namespace App\Models\Share;

use App\Enums\ItemEffectType;
use App\Enums\ItemEffectValueType;
use App\Services\ItemEffect\ValueObjects\ItemEffectValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $share_item_id
 * @property ItemEffectType $effect_type
 * @property int $value
 * @property ItemEffectValueType $value_type
 * @property int|null $duration_seconds
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
        );
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
