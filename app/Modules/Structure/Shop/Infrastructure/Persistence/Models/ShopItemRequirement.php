<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Infrastructure\Persistence\Models;

use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopItemRequirement extends Model
{
    use HasFactory;

    protected $fillable = ['share_item_id', 'quantity'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }
}
