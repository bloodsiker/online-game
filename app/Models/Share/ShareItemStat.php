<?php

declare(strict_types=1);

namespace App\Models\Share;

use App\Enums\ItemEffectValueType;
use App\Enums\ShareItemStatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ShareItemStatType  $stat_type
 * @property int                $value
 * @property ItemEffectValueType $value_type
 */
class ShareItemStat extends Model
{
    protected $fillable = [
        'share_item_id',
        'stat_type',
        'value',
        'value_type',
    ];

    protected $casts = [
        'stat_type'  => ShareItemStatType::class,
        'value_type' => ItemEffectValueType::class,
    ];

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function isPercent(): bool
    {
        return $this->value_type === ItemEffectValueType::PERCENT;
    }
}