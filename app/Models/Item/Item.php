<?php

namespace App\Models\Item;

use App\Models\Share\ShareItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $upgrade_lvl
 * @property int $share_item_id
 * @property int $additional_attack
 * @property int $count_use
 * @property bool $is_open
 * @property int $count
 *
 * @property-read ShareItem $itemInfo
 * @property-read Collection|Item[] $itemsInChest
 */
class Item extends Model
{
    use HasFactory;

    protected $fillable = ['share_item_id'];

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
