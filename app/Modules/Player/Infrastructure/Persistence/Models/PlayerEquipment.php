<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerEquipment extends Model
{
    use HasFactory;

    /** @var array<string, string> relation name => database column */
    public const ITEM_SLOT_RELATIONS = [
        'handLeft' => 'hand_left',
        'handRight' => 'hand_right',
        'helmetSlot' => 'helmet',
        'shoulderSlot' => 'shoulder',
        'forearmSlot' => 'forearm',
        'armorSlot' => 'armor',
        'leggingSlot' => 'legging',
        'chainArmorSlot' => 'chain_armor',
        'cloakSlot' => 'cloak',
        'shoesSlot' => 'shoes',
        'glovesSlot' => 'gloves',
        'beltFirstSlot' => 'belt_first',
        'beltSecondSlot' => 'belt_second',
        'bagFirstSlot' => 'bag_first',
        'bagSecondSlot' => 'bag_second',
    ];

    protected $table = 'player_equipments';

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id')->with(['race']);
    }

    public function handLeft(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'hand_left')->with(['itemInfo']);
    }

    public function handRight(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'hand_right')->with(['itemInfo']);
    }

    public function helmetSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'helmet')->with(['itemInfo']);
    }

    public function shoulderSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'shoulder')->with(['itemInfo']);
    }

    public function forearmSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'forearm')->with(['itemInfo']);
    }

    public function armorSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'armor')->with(['itemInfo']);
    }

    public function leggingSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'legging')->with(['itemInfo']);
    }

    public function chainArmorSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'chain_armor')->with(['itemInfo']);
    }

    public function cloakSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'cloak')->with(['itemInfo']);
    }

    public function shoesSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'shoes')->with(['itemInfo']);
    }

    public function glovesSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'gloves')->with(['itemInfo']);
    }

    public function beltFirstSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'belt_first')->with(['itemInfo']);
    }

    public function beltSecondSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'belt_second')->with(['itemInfo']);
    }

    public function bagFirstSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'bag_first')->with(['itemInfo']);
    }

    public function bagSecondSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'bag_second')->with(['itemInfo']);
    }
}
