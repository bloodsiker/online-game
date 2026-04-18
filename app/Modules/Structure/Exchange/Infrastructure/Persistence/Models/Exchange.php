<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exchange extends Model
{
    use HasFactory;

    protected $fillable = ['structure_id', 'from_share_item_id', 'to_share_item_id', 'from_amount', 'to_amount', 'sort_order'];

    protected $attributes = [
        'sort_order' => 0,
    ];

    public function fromItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'from_share_item_id');
    }

    public function toItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'to_share_item_id');
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
