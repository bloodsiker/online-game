<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\MapGatheringResource;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ShareItemType $type
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property string|null $transparent_image
 * @property int $is_two_hand
 * @property int $count_use
 * @property int|null $max_drop_level_difference Maximum level the player can exceed the monster by for this item to drop.
 * @property int|null $expire
 * @property bool $is_active
 * @property bool $is_sell
 * @property bool $is_give
 * @property bool $is_droppable
 * @property bool $is_stackable
 * @property bool $is_slot_usable
 * @property bool $is_weight
 * @property int $price
 * @property int $break_crystal
 * @property UpgradeScrollType|null $upgrade_scroll_type
 * @property array|null $gem_stats
 * @property RuneRarity|null $rune_rarity
 * @property array|null $rune_stat_pool
 * @property ItemRarity $rarity
 * @property int|null $upgrade_to_share_item_id
 * @property int $upgrade_gold_cost
 * @property ShareItemSlot|null $slot
 * @property int|null $skill_id
 * @property int|null $skill_lvl
 * @property int|null $skill_exp
 * @property int|null $gathering_time_seconds
 * @property int|null $gathering_respawn_seconds
 * @property string|null $tool_family
 * @property int $gathering_speed_bonus_percent
 * @property string|null $gathering_tool_family
 * @property-read Skill|null $skill
 * @property-read Collection|ShareItemEffect[] $effects
 * @property-read Collection|ShareItemBuff[] $buffs
 * @property-read Collection|ShareItemDebuff[] $debuffs
 * @property-read Collection|ShareItemStat[] $stats
 * @property-read Collection|ShareItemRequirement[] $requirements
 */
class ShareItem extends Model
{
    /** Единственное место для дефолтной иконки — если у предмета нет своей картинки. Замена картинки — правка только здесь. */
    public const DEFAULT_IMAGE = '/img/bg/empty_slot.gif';

    public function scopeByGroup($query, string $group)
    {
        return $query->whereIn('type', ShareItemType::values(ShareItemType::group($group)));
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null && $value !== '' ? resolve_storage_image_url($value) : self::DEFAULT_IMAGE,
        );
    }

    protected function transparentImage(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null && $value !== '' ? resolve_storage_image_url($value) : null,
        );
    }

    protected $attributes = [
        'count_use' => 0,
        'is_active' => true,
        'is_sell' => true,
        'is_auction_sellable' => false,
        'is_give' => true,
        'is_droppable' => true,
        'is_stackable' => false,
        'is_slot_usable' => false,
        'is_weight' => true,
        'break_crystal' => 0,
        'price' => 0,
    ];

    protected $casts = [
        'is_slot_usable' => 'boolean',
        'is_weight' => 'boolean',
        'is_sell' => 'boolean',
        'is_give' => 'boolean',
        'is_droppable' => 'boolean',
        'is_stackable' => 'boolean',
        'is_active' => 'boolean',
        'expire' => 'integer',
        'type' => ShareItemType::class,
        'slot' => ShareItemSlot::class,
        'upgrade_scroll_type' => UpgradeScrollType::class,
        'gem_stats' => 'array',
        'rune_rarity' => RuneRarity::class,
        'rune_stat_pool' => 'array',
        'rarity' => ItemRarity::class,
        'gathering_time_seconds' => 'integer',
        'gathering_respawn_seconds' => 'integer',
        'gathering_speed_bonus_percent' => 'integer',
        'upgrade_gold_cost' => 'integer',
    ];

    protected $fillable = ['name', 'description', 'is_two_hand', 'type', 'image', 'skill_id', 'skill_lvl', 'skill_exp'];

    public function recipe(): HasOne
    {
        return $this->hasOne(ShareRecipe::class, 'share_item_id');
    }

    public function magicSkillBook(): HasOne
    {
        return $this->hasOne(MagicSkillBook::class, 'share_item_id');
    }

    public function mapGatheringResources(): HasMany
    {
        return $this->hasMany(MapGatheringResource::class);
    }

    public function skill(): ?BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    /** Предмет, в который превращается экземпляр при апгрейде редкости. */
    public function rarityUpgradeTarget(): BelongsTo
    {
        return $this->belongsTo(self::class, 'upgrade_to_share_item_id');
    }

    /** Материалы, дополнительно требуемые для апгрейда этого предмета. */
    public function rarityUpgradeMaterials(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'share_item_upgrade_materials',
            'share_item_id',
            'required_share_item_id',
        )->withPivot('count')->withTimestamps();
    }

    public function effects(): HasMany
    {
        return $this->hasMany(ShareItemEffect::class);
    }

    public function buffs(): HasMany
    {
        return $this->hasMany(ShareItemBuff::class);
    }

    public function debuffs(): HasMany
    {
        return $this->hasMany(ShareItemDebuff::class);
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

    public function groundExpiresAt(): ?Carbon
    {
        return $this->expire === null
            ? null
            : now()->addMinutes($this->expire);
    }
}
