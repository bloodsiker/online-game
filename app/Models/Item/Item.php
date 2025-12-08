<?php

namespace App\Models\Item;

use App\Models\ShareItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    use HasFactory;

    protected $with = ['itemInfo'];

    protected $attributes = [
        'upgrade_lvl' => 0,
        'additional_attack' => 0,
        'count_use' => 0,
        'is_open' => 0,
    ];

    public function getName(): string
    {
        if ($this->itemInfo->count_use) {
            return sprintf('%s (%s)', $this->itemInfo->name, $this->count_use);
        }

        return $this->itemInfo->name;
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id')->with(['recipe', 'recipe.items']);
    }

    public function itemsInChest(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_in_chest', 'chest_id', 'item_id')->withPivot(['count']);
    }
}
