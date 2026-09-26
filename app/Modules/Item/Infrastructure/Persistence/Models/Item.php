<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $upgrade_lvl
 * @property int $upgrade_pity
 * @property int $upgrade_fail_streak
 * @property int $socket_count
 * @property int $rune_slot_count
 * @property int $share_item_id
 * @property int $additional_attack
 * @property int $count_use
 * @property bool $is_open
 * @property Carbon|null $expires_at
 * @property int $count
 * @property-read ShareItem $itemInfo
 * @property-read Collection|Item[] $itemsInChest
 * @property-read Collection|ItemGem[] $gems
 * @property-read Collection|ItemRune[] $runes
 */
class Item extends Model
{
    use HasFactory;

    protected $fillable = ['share_item_id'];

    protected $with = ['itemInfo'];

    protected $attributes = [
        'upgrade_lvl' => 0,
        'upgrade_pity' => 0,
        'upgrade_fail_streak' => 0,
        'socket_count' => 0,
        'rune_slot_count' => 0,
        'additional_attack' => 0,
        'count_use' => 0,
        'is_open' => 0,
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function getName(): string
    {
        if ($this->itemInfo->count_use) {
            return sprintf('%s (%s)', $this->itemInfo->name, $this->count_use);
        }

        return $this->itemInfo->name;
    }

    public function remainingUses(): ?int
    {
        $configuredUses = max(0, (int) $this->itemInfo->count_use);

        if ($configuredUses === 0) {
            return null;
        }

        return (int) $this->count_use > 0
            ? (int) $this->count_use
            : $configuredUses;
    }

    /** @return list<array{title: string, value: string}> */
    public function expiryTooltipInfo(): array
    {
        return $this->expires_at === null ? [] : [[
            'title' => 'Исчезнет',
            'value' => $this->expires_at->format('d.m.Y H:i'),
        ]];
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id')->with(['recipe', 'recipe.items']);
    }

    public function itemsInChest(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'item_in_chest', 'chest_id', 'item_id')->withPivot(['count']);
    }

    public function gems(): HasMany
    {
        return $this->hasMany(ItemGem::class)->with('gemInfo')->orderBy('socket_index');
    }

    public function runes(): HasMany
    {
        return $this->hasMany(ItemRune::class)->with('runeInfo')->orderBy('slot_index');
    }
}
