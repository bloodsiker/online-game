<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShareRecipe extends Model
{
    use HasFactory;

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class,'share_item_id');
    }

    public function kraftItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class,'kraft_item_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'share_recipe_has_items', 'share_recipe_id', 'share_item_id')->withPivot('count');
    }
}
