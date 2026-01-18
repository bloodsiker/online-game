<?php

namespace App\Models\Share;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Monster\Monster;

/**
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property int $is_two_hand
 * @property int $min_attack
 * @property int $max_attack
 * @property int $armor
 * @property int $count_use
 * @property bool $is_heal
 * @property bool $is_active
 * @property bool $is_sell
 * @property int $price
 * @property int $break_crystal
 * @property string|null $slot
 * @property int|null $skill_id
 * @property int|null $skill_lvl
 * @property int|null $skill_exp
 *
 * @property-read Skill|null $skill
 * @property-read Collection|ShareItemEffect[] $effects
 */
class ShareItem extends Model
{
    use HasFactory;

    public const TYPE_RESOURCE = 'resource';
    public const TYPE_WEAPON = 'weapon';
    public const TYPE_SHIELD = 'shield';
    public const TYPE_ARMOR = 'armor';
    public const TYPE_BELT = 'belt';
    public const TYPE_BAG = 'bag';
    public const TYPE_KEY = 'key';
    public const TYPE_HEAL = 'heal';
    public const TYPE_QUEST = 'quest';
    public const TYPE_ARTIFACT = 'artifact';
    public const TYPE_RECIPE = 'recipe';
    public const TYPE_CHEST = 'chest';
    public const TYPE_SCROLL = 'scroll';

    public const SLOT_HAND = 'hand';

    public const TYPES = [
        self::TYPE_RESOURCE => 'Ресурсы',
        self::TYPE_WEAPON => 'Оружие',
        self::TYPE_SHIELD => 'Щит',
        self::TYPE_ARMOR => 'Броня',
        self::TYPE_BELT => 'Пояс',
        self::TYPE_BAG => 'Сумка',
        self::TYPE_KEY => 'Ключ',
        self::TYPE_HEAL => 'Восстанавливающие',
        self::TYPE_QUEST => 'Квест',
        self::TYPE_ARTIFACT => 'Артефакт',
        self::TYPE_RECIPE => 'Рецепт',
        self::TYPE_CHEST => 'Сундук',
        self::TYPE_SCROLL => 'Свиток',
    ];

    const GROUPS = [
        'main' => [
            self::TYPE_HEAL,
            self::TYPE_WEAPON,
            self::TYPE_SHIELD,
            self::TYPE_ARMOR,
            self::TYPE_BELT,
            self::TYPE_BAG,
            self::TYPE_RESOURCE,
            self::TYPE_RECIPE,
            self::TYPE_SCROLL
        ],
        'key' => [self::TYPE_KEY],
        'quest' => [self::TYPE_QUEST],
        'artifact' => [self::TYPE_ARTIFACT],
        'gift' => ['gift'],
    ];

    public function scopeByGroup($query, string $group)
    {
        return $query->whereIn(
            'type',
            self::GROUPS[$group] ?? self::GROUPS['main']
        );
    }

//    protected $with = ['recipe'];

    protected $attributes = [
        'count_use' => 0,
        'is_heal' => 0,
        'is_active' => 1,
        'is_sell' => 1,
        'is_slot_usable' => 0,
        'break_crystal' => 0,
        'price' => 0,
    ];

    protected $casts = [
        'is_slot_usable' => 'boolean',
        'is_sell' => 'boolean',
        'is_active' => 'boolean',
        'is_heal' => 'boolean',
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

    public function monsters()
    {
        return $this->belongsToMany(Monster::class, 'monster_has_items', 'share_item_id', 'monster_id')->withPivot(['min_count', 'max_count', 'drop_chance']);
    }

    public function itemHasItems(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'share_item_has_items', 'parent_item_id', 'share_item_id')->withPivot(['min_count', 'max_count', 'drop_chance']);
    }

    public function getTypeName(): string
    {
        if (array_key_exists($this->type, self::TYPES)) {
            return self::TYPES[$this->type];
        }

        return $this->type;
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
