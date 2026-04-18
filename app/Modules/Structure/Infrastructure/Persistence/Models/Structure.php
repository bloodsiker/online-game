<?php

declare(strict_types=1);

namespace App\Modules\Structure\Infrastructure\Persistence\Models;

use App\Models\Npc;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareAction;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Structure\Exchange\Infrastructure\Persistence\Models\Exchange;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Structure extends Model
{
    use HasFactory;

    public const TYPE_SHOP = 'shop';

    public const TYPE_AUCTION = 'auction';

    public const TYPE_HEAL = 'heal';

    public const TYPE_WAREHOUSE = 'warehouse';

    public const TYPE_CLAN_WAREHOUSE = 'clan_warehouse';

    public const TYPE_BANK = 'bank';

    public const TYPE_CLAN_BANK = 'clan_bank';

    public const TYPE_BLACKSMITH = 'blacksmith';

    public const TYPE_EXCHANGE = 'exchange';

    public const TYPES = [
        self::TYPE_SHOP => 'Магазин',
        self::TYPE_AUCTION => 'Аукцион',
        self::TYPE_HEAL => 'Восстановление',
        self::TYPE_WAREHOUSE => 'Хранилище',
        self::TYPE_CLAN_WAREHOUSE => 'Клановое хранилище',
        self::TYPE_BANK => 'Банк',
        self::TYPE_CLAN_BANK => 'Клановая казна',
        self::TYPE_BLACKSMITH => 'Кузня',
        self::TYPE_EXCHANGE => 'Обмен',
    ];

    protected $with = ['location'];

    protected $attributes = [
        'type' => self::TYPE_SHOP,
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'npc_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ShareStructureCategory::class, 'shop_categories', 'structure_id', 'share_structure_category_id');
    }

    public function shopItems(): HasMany
    {
        return $this->hasMany(ShopItem::class);
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(
            ShareItem::class,
            ShopItem::class,
            'structure_id',
            'id',
            'id',
            'share_item_id'
        );
    }

    public function exchangeItems(): HasMany
    {
        return $this->hasMany(Exchange::class);
    }

    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(ShareAction::class, 'structure_actions', 'structure_id', 'share_action_id');
    }

    public function getTypeName(): string
    {
        if (array_key_exists($this->type, self::TYPES)) {
            return self::TYPES[$this->type];
        }

        return $this->type;
    }

    public function isShop(): bool
    {
        return $this->type === self::TYPE_SHOP;
    }

    public function isExchange(): bool
    {
        return $this->type === self::TYPE_EXCHANGE;
    }

    public function isAuction(): bool
    {
        return $this->type === self::TYPE_AUCTION;
    }

    public function isHeal(): bool
    {
        return $this->type === self::TYPE_HEAL;
    }

    public function isWarehouse(): bool
    {
        return $this->type === self::TYPE_WAREHOUSE;
    }

    public function isClanWarehouse(): bool
    {
        return $this->type === self::TYPE_CLAN_WAREHOUSE;
    }

    public function isBlacksmith(): bool
    {
        return $this->type === self::TYPE_BLACKSMITH;
    }

    public function isBank(): bool
    {
        return $this->type === self::TYPE_BANK;
    }

    public function isClanBank(): bool
    {
        return $this->type === self::TYPE_CLAN_BANK;
    }
}
