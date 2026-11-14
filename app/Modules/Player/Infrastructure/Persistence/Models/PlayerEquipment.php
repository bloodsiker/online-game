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
        'ring1Slot' => 'ring_1',
        'ring2Slot' => 'ring_2',
        'ring3Slot' => 'ring_3',
        'ring4Slot' => 'ring_4',
        'ring5Slot' => 'ring_5',
        'earring1Slot' => 'earring_1',
        'earring2Slot' => 'earring_2',
        'necklace1Slot' => 'necklace_1',
        'necklace2Slot' => 'necklace_2',
        'quiverSlot' => 'quiver',
    ];

    /** @var list<string> реляции ювелирных слотов (кольца, серьги, ожерелья) */
    public const JEWELRY_RELATIONS = [
        'ring1Slot',
        'ring2Slot',
        'ring3Slot',
        'ring4Slot',
        'ring5Slot',
        'earring1Slot',
        'earring2Slot',
        'necklace1Slot',
        'necklace2Slot',
        'quiverSlot',
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

    public function ring1Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ring_1')->with(['itemInfo']);
    }

    public function ring2Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ring_2')->with(['itemInfo']);
    }

    public function ring3Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ring_3')->with(['itemInfo']);
    }

    public function ring4Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ring_4')->with(['itemInfo']);
    }

    public function ring5Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ring_5')->with(['itemInfo']);
    }

    public function earring1Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'earring_1')->with(['itemInfo']);
    }

    public function earring2Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'earring_2')->with(['itemInfo']);
    }

    public function necklace1Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'necklace_1')->with(['itemInfo']);
    }

    public function necklace2Slot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'necklace_2')->with(['itemInfo']);
    }

    public function quiverSlot(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'quiver')->with(['itemInfo']);
    }

    /**
     * Надетые кольца, серьги, ожерелья и колчан.
     *
     * @return list<Item>
     */
    public function jewelryItems(): array
    {
        $items = [];
        foreach (self::JEWELRY_RELATIONS as $relation) {
            $item = $this->$relation;
            if ($item instanceof Item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Дворучное оружие из правой руки для отображения в пустой левой
     * (манекен показывает его во второй руке с пониженной прозрачностью).
     */
    public function getHandLeftMirrorAttribute(): ?Item
    {
        return $this->handLeft === null
            && $this->handRight !== null
            && $this->handRight->itemInfo?->is_two_hand
                ? $this->handRight
                : null;
    }

    /** @see getHandLeftMirrorAttribute() */
    public function getHandRightMirrorAttribute(): ?Item
    {
        return $this->handRight === null
            && $this->handLeft !== null
            && $this->handLeft->itemInfo?->is_two_hand
                ? $this->handLeft
                : null;
    }
}
