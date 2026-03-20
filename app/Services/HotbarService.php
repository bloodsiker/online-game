<?php

namespace App\Services;

use App\Enums\ItemEffectType;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\Player\Player;
use App\Models\Player\PlayerSlot;

class HotbarService
{
    const BASE_SLOTS = 3;

    /**
     * Calculates the total number of hotbar slots available to the player.
     * Base: 3 slots. Belt items with BELT_SLOT effect add more.
     */
    public function maxSlots(Player $player): int
    {
        $equip = $player->playerEquip;
        if (!$equip) {
            return self::BASE_SLOTS;
        }

        $extra = 0;
        foreach (['beltFirstSlot', 'beltSecondSlot'] as $relation) {
            /** @var Item|null $beltItem */
            $beltItem = $equip->$relation;
            if (!$beltItem) {
                continue;
            }

            $beltItem->itemInfo->loadMissing('effects');
            $extra += $beltItem->itemInfo->effects
                ->filter(fn($e) => $e->effect_type === ItemEffectType::BELT_SLOT)
                ->sum('value');
        }

        return self::BASE_SLOTS + $extra;
    }

    /**
     * Returns hotbar data for the player: max slots + each slot's content.
     */
    public function getSlotsData(Player $player): array
    {
        $maxSlots = $this->maxSlots($player);
        $filled = $player->hotbarSlots->keyBy('slot_number');

        $slots = [];
        for ($i = 1; $i <= $maxSlots; $i++) {
            /** @var PlayerSlot|null $slot */
            $slot = $filled->get($i);
            $slots[] = $this->formatSlot($i, $slot, $player);
        }

        return [
            'max_slots' => $maxSlots,
            'slots'     => $slots,
        ];
    }

    /**
     * Assigns an entity (item from backpack or skill) to a hotbar slot.
     * Returns an error string on failure, null on success.
     */
    public function setSlot(Player $player, int $slotNumber, string $entityType, int $entityId): ?string
    {
        if ($slotNumber < 1 || $slotNumber > $this->maxSlots($player)) {
            return 'Слот недоступен.';
        }

        $alreadyInSlot = PlayerSlot::where('player_id', $player->id)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('slot_number', '!=', $slotNumber)
            ->exists();

        if ($alreadyInSlot) {
            return 'Этот предмет уже назначен на другой слот.';
        }

        if ($entityType === 'item') {
            $backpack = Backpack::where('user_id', $player->user_id)
                ->where('item_id', $entityId)
                ->first();

            if (!$backpack) {
                return 'Предмет не найден в рюкзаке.';
            }

            if (!$backpack->item->itemInfo->is_slot_usable) {
                return 'Этот предмет нельзя добавить на панель.';
            }
        } elseif ($entityType === 'skill') {
            $hasSkill = $player->magicSkills()->where('magic_skill_id', $entityId)->exists();
            if (!$hasSkill) {
                return 'Навык не найден.';
            }
        } else {
            return 'Неизвестный тип.';
        }

        PlayerSlot::updateOrCreate(
            ['player_id' => $player->id, 'slot_number' => $slotNumber],
            ['entity_type' => $entityType, 'entity_id' => $entityId],
        );

        return null;
    }

    /**
     * Removes an entity from a hotbar slot.
     */
    public function clearSlot(Player $player, int $slotNumber): void
    {
        PlayerSlot::where('player_id', $player->id)
            ->where('slot_number', $slotNumber)
            ->delete();
    }

    private function formatSlot(int $slotNumber, ?PlayerSlot $slot, Player $player): array
    {
        if (!$slot) {
            return [
                'slot'        => $slotNumber,
                'empty'       => true,
                'entity_type' => null,
                'entity_id'   => null,
                'name'        => null,
                'image'       => null,
                'cooldown'    => 0,
            ];
        }

        $name     = null;
        $image    = null;
        $cooldown = 0;

        if ($slot->entity_type === 'item') {
            $item = Item::with('itemInfo')->find($slot->entity_id);
            if ($item) {
                $name     = $item->itemInfo->name;
                $image    = $item->itemInfo->image;
                $cooldown = 1; // TODO: получать из атрибутов предмета
            }
        } elseif ($slot->entity_type === 'skill') {
            $skill = $player->magicSkills()->where('magic_skill_id', $slot->entity_id)->first()
                ?? \App\Models\MagicSkill\MagicSkill::find($slot->entity_id);
            if ($skill) {
                $name  = $skill->name;
                $image = $skill->image ?? null;
            }
        }

        return [
            'slot'        => $slotNumber,
            'empty'       => false,
            'entity_type' => $slot->entity_type,
            'entity_id'   => $slot->entity_id,
            'name'        => $name,
            'image'       => $image,
            'cooldown'    => $cooldown,
        ];
    }
}