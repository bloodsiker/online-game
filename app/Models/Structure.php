<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Structure extends Model
{
    use HasFactory;

    public const TYPE_SHOP = 'shop';
    public const TYPE_AUCTION = 'auction';
    public const TYPE_HEAL = 'heal';
    public const TYPE_WAREHOUSE = 'warehouse';
    public const TYPE_BANK = 'bank';
    public const TYPE_BLACKSMITH = 'blacksmith';

    public const TYPES = [
        self::TYPE_SHOP => 'Магазин',
        self::TYPE_AUCTION => 'Аукцион',
        self::TYPE_HEAL => 'Востановление',
        self::TYPE_WAREHOUSE => 'Хранилище',
        self::TYPE_BANK => 'Банк',
        self::TYPE_BLACKSMITH => 'Кузня',
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

    public function shopItems(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'shop_items', 'structure_id', 'share_item_id');
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

    public function isShop()
    {
        return $this->type === self::TYPE_SHOP;
    }

    public function isAuction()
    {
        return $this->type === self::TYPE_AUCTION;
    }

    public function isHeal()
    {
        return $this->type === self::TYPE_HEAL;
    }

    public function isWarehouse()
    {
        return $this->type === self::TYPE_WAREHOUSE;
    }

    public function isBlacksmith()
    {
        return $this->type === self::TYPE_BLACKSMITH;
    }
}
