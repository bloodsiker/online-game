<?php

namespace App\Models\Share;

use App\Enums\ItemEffectType;
use App\Enums\ItemEffectValueType;
use App\ItemEffect\ValueObjects\ItemEffectValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $share_item_id
 * @property ItemEffectType $effect_type
 * @property int $value
 * @property ItemEffectValueType $value_type
 *
 * @property-read ShareItem $itemInfo
 */
class ShareItemEffect extends Model
{
    protected $fillable = [
        'share_item_id',
        'effect_type',
        'value',
        'value_type',
    ];

    protected $casts = [
        'effect_type' => ItemEffectType::class,
        'value_type'  => ItemEffectValueType::class,
    ];

    public function toValueObject(): ItemEffectValue
    {
        return new ItemEffectValue(
            $this->effect_type,
            $this->value_type,
            $this->value,
        );
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
