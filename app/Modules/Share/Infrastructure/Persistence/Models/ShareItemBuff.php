<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Бафф, который накладывается на игрока при использовании предмета.
 *
 * @property int $share_item_id
 * @property int $effect_id
 * @property int $duration_seconds
 */
class ShareItemBuff extends Model
{
    protected $fillable = [
        'share_item_id',
        'effect_id',
        'duration_seconds',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function effect(): BelongsTo
    {
        return $this->belongsTo(Effect::class);
    }
}
