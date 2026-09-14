<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Share\Domain\Enums\ItemBuffReapplyPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Бафф, который накладывается на игрока при использовании предмета.
 *
 * @property int $share_item_id
 * @property int $effect_id
 * @property int $duration_seconds
 * @property ItemBuffReapplyPolicy $reapply_policy
 */
class ShareItemBuff extends Model
{
    protected $fillable = [
        'share_item_id',
        'effect_id',
        'duration_seconds',
        'reapply_policy',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'reapply_policy' => ItemBuffReapplyPolicy::class,
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
