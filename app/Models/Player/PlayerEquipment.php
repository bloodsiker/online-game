<?php

namespace App\Models\Player;

use App\Models\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerEquipment extends Model
{
    use HasFactory;

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
}
