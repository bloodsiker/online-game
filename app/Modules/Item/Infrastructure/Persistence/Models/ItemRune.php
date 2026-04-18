<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_id
 * @property int $slot_index
 * @property int $share_item_id
 * @property array $stats
 * @property array|null $passive_skill
 * @property int $reroll_count
 * @property-read ShareItem $runeInfo
 */
class ItemRune extends Model
{
    protected $fillable = ['item_id', 'slot_index', 'share_item_id', 'stats', 'passive_skill', 'reroll_count'];

    protected $casts = [
        'stats' => 'array',
        'passive_skill' => 'array',
    ];

    public function runeInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
