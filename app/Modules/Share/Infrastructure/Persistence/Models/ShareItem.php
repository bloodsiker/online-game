<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Enums\ItemRarity;
use App\Enums\ShareItemSlot;
use App\Enums\ShareItemType;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Models\Skill;
use App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property ShareItemType $type
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property int $is_two_hand
 * @property int $count_use
 * @property bool $is_heal
 * @property bool $is_active
 * @property bool $is_sell
 * @property bool $is_slot_usable
 * @property bool $is_weight
 * @property int $price
 * @property int $break_crystal
 * @property UpgradeScrollType|null $upgrade_scroll_type
 * @property array|null $gem_stats
 * @property RuneRarity|null $rune_rarity
 * @property array|null $rune_stat_pool
 * @property ItemRarity $rarity
 * @property ShareItemSlot|null $slot
 * @property int|null $skill_id
 * @property int|null $skill_lvl
 * @property int|null $skill_exp
 * @property-read Skill|null $skill
 * @property-read Collection|ShareItemEffect[] $effects
 * @property-read Collection|ShareItemStat[] $stats
 * @property-read Collection|ShareItemRequirement[] $requirements
 */
class ShareItem extends Model
{
    public function scopeByGroup($query, string $group)
    {
        return $query->whereIn('type', ShareItemType::values(ShareItemType::group($group)));
    }

    protected $attributes = [
        'count_use' => 0,
        'is_heal' => false,
        'is_active' => true,
        'is_sell' => true,
        'is_slot_usable' => false,
        'is_weight' => true,
        'break_crystal' => 0,
        'price' => 0,
    ];

    protected $casts = [
        'is_slot_usable' => 'boolean',
        'is_weight' => 'boolean',
        'is_sell' => 'boolean',
        'is_active' => 'boolean',
        'is_heal' => 'boolean',
        'type' => ShareItemType::class,
        'slot' => ShareItemSlot::class,
        'upgrade_scroll_type' => UpgradeScrollType::class,
        'gem_stats' => 'array',
        'rune_rarity' => RuneRarity::class,
        'rune_stat_pool' => 'array',
        'rarity' => ItemRarity::class,
    ];

    protected $fillable = ['name', 'description', 'is_two_hand', 'type', 'image', 'skill_id', 'skill_lvl', 'skill_exp'];

    public function recipe(): HasOne
    {
        return $this->hasOne(ShareRecipe::class, 'share_item_id');
    }

    public function skill(): ?BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    public function effects(): HasMany
    {
        return $this->hasMany(ShareItemEffect::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(ShareItemStat::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ShareItemRequirement::class)->with('skill');
    }

    public function monsters()
    {
        return $this->belongsToMany(Monster::class, 'monster_has_items', 'share_item_id', 'monster_id')
            ->withPivot(['min_count', 'max_count', 'drop_chance']);
    }

    public function itemHasItems(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'share_item_has_items', 'parent_item_id', 'share_item_id')
            ->withPivot(['min_count', 'max_count', 'drop_chance']);
    }

    public function getTypeName(): string
    {
        return $this->type->label();
    }

    public function getCountItemPerRecipe(array $items)
    {
        foreach ($items as $item) {
            if ($this->id == $item['id']) {
                return $item['count'];
            }
        }

        return 0;
    }
}
