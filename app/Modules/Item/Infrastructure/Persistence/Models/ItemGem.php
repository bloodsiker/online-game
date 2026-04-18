<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_id
 * @property int $socket_index
 * @property int $share_item_id
 * @property-read ShareItem $gemInfo
 */
class ItemGem extends Model
{
    protected $fillable = ['item_id', 'socket_index', 'share_item_id'];

    public function gemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
