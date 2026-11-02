<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureHunt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        if ($this->itemInfo->type === ShareItemType::TREASURE_MAP) {
            return $this->itemInfo->name;
        }
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

    public function getDescription(): string
    {
        $description = (string) $this->itemInfo->description;
        if ($this->itemInfo->type !== ShareItemType::TREASURE_MAP) {
            return $description;
        }
        $hunt = $this->treasureHunt;
        $details = '';
        if ($territory = $hunt?->targetMap?->name) {
            $details .= '<p>'.__('treasure.territory_description', ['name' => e($territory)]).'</p>';
        }
        if ($hunt?->activated_at !== null && $hunt->target_location_id !== null) {
            $details .= '<p><b>['.e(__('treasure.location_number', ['id' => (int) $hunt->target_location_id])).']</b></p>';
        }

        return $details.$description;
    }

    /** @return list<array{title: string, value: string}> */
    public function expiryTooltipInfo(): array
    {
        return $this->expires_at === null ? [] : [[
            'title' => __('Исчезнет'),
            'value' => $this->expires_at->format('d.m.Y H:i'),
        ]];
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id')->with(['recipe', 'recipe.items']);
    }

    public function treasureHunt(): HasOne
    {
        return $this->hasOne(TreasureHunt::class, 'map_item_id');
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
