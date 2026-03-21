<?php

namespace App\Services;

use App\DTO\StatModifier;
use App\DTO\StatSheet;
use App\Enums\ItemEffectType;
use App\Enums\ItemEffectValueType;
use App\Models\Item\Item;
use App\Models\Player\Player;

class PlayerStatService
{
    /**
     * Scale hp_now/mp_now proportionally when equipped passive skills change.
     * Call before and after the skill change: pass old max and new max.
     */
    public function scaleHp(Player $player, int $oldHpMax, int $newHpMax, int $oldMpMax, int $newMpMax): void
    {
        if ($oldHpMax > 0 && $oldHpMax !== $newHpMax) {
            $player->hp_now = min($newHpMax, (int) round($player->hp_now * $newHpMax / $oldHpMax));
        }

        if ($oldMpMax > 0 && $oldMpMax !== $newMpMax) {
            $player->mp_now = min($newMpMax, (int) round($player->mp_now * $newMpMax / $oldMpMax));
        }

        $player->save();
    }

    /**
     * Build a fully resolved StatSheet for a player,
     * applying equipment, passive skills and buffs.
     */
    public function resolve(Player $player): StatSheet
    {
        $modifiers = [
            ...$this->fromEquipment($player),
            ...$this->fromPassiveSkills($player),
            ...$this->fromBuffs($player),
        ];

        return $this->buildSheet($player, $modifiers);
    }

    // -------------------------------------------------------------------------
    // Sheet assembly
    // -------------------------------------------------------------------------

    private function buildSheet(Player $player, array $modifiers): StatSheet
    {
        $equip = $player->playerEquip;

        // Base values. Weapon slots: if a weapon is equipped the base is 0
        // (weapon damage comes entirely from the item's ATTACK_MIN/MAX effects).
        $base = [
            'strength'      => (float) floor($player->str),
            'int'           => (float) floor($player->int),
            'agility'       => (float) floor($player->agil),
            'mud'           => (float) floor($player->mud),
            'intelligence'  => (float) floor($player->intel),
            'dodge'         => (float) $player->dodge,
            'critical'      => (float) $player->critical,
            'armor'         => 0.0,
            'hp_max'        => (float) $player->hp_max,
            'mp_max'        => (float) $player->mp_max,
            'left_min_dmg'  => $equip?->handLeft  instanceof Item ? 0.0 : (float) $player->min_dmg,
            'left_max_dmg'  => $equip?->handLeft  instanceof Item ? 0.0 : (float) $player->max_dmg,
            'right_min_dmg' => $equip?->handRight instanceof Item ? 0.0 : (float) $player->min_dmg,
            'right_max_dmg' => $equip?->handRight instanceof Item ? 0.0 : (float) $player->max_dmg,
        ];

        // Accumulate flat and percent per stat
        $flat    = array_fill_keys(array_keys($base), 0.0);
        $percent = array_fill_keys(array_keys($base), 0.0);

        foreach ($modifiers as $m) {
            if (!array_key_exists($m->stat, $base)) {
                continue;
            }
            if ($m->isPercent) {
                $percent[$m->stat] += $m->value;
            } else {
                $flat[$m->stat] += $m->value;
            }
        }

        // Formula: floor( (base + flat) * (1 + percent / 100) )
        $computed = [];
        foreach ($base as $stat => $baseVal) {
            $computed[$stat] = (int) floor(
                ($baseVal + $flat[$stat]) * (1 + $percent[$stat] / 100)
            );
        }

        $sheet = new StatSheet();
        $sheet->modifiers    = $modifiers;
        $sheet->freeStats    = $player->free_stats;
        $sheet->strength     = $computed['strength'];
        $sheet->int          = $computed['int'];
        $sheet->agility      = $computed['agility'];
        $sheet->mud          = $computed['mud'];
        $sheet->intelligence = $computed['intelligence'];
        $sheet->dodge        = $computed['dodge'];
        $sheet->critical     = $computed['critical'];
        $sheet->armor        = $computed['armor'];
        $sheet->hpMax        = $computed['hp_max'];
        $sheet->mpMax        = $computed['mp_max'];
        $sheet->leftMinDmg   = $computed['left_min_dmg'];
        $sheet->leftMaxDmg   = $computed['left_max_dmg'];
        $sheet->rightMinDmg  = $computed['right_min_dmg'];
        $sheet->rightMaxDmg  = $computed['right_max_dmg'];

        return $sheet;
    }

    // -------------------------------------------------------------------------
    // Modifier sources
    // -------------------------------------------------------------------------

    /** @return StatModifier[] */
    private function fromEquipment(Player $player): array
    {
        $modifiers = [];
        $equip = $player->playerEquip;

        if (!$equip) {
            return $modifiers;
        }

        // All slots for armor/misc effects
        $allSlots = array_filter([
            $equip->handLeft, $equip->handRight,
            $equip->helmetSlot, $equip->shoulderSlot, $equip->forearmSlot,
            $equip->armorSlot, $equip->leggingSlot, $equip->chainArmorSlot,
            $equip->cloakSlot, $equip->shoesSlot, $equip->glovesSlot,
            $equip->beltFirstSlot, $equip->beltSecondSlot,
            $equip->bagFirstSlot, $equip->bagSecondSlot,
        ]);

        foreach ($allSlots as $item) {
            $source = 'equipment:' . $item->itemInfo->name;

            foreach ($item->itemInfo->effects as $effect) {
                if ($effect->effect_type === ItemEffectType::ARMOR) {
                    $modifiers[] = new StatModifier(
                        stat: 'armor',
                        value: $effect->value,
                        isPercent: $effect->value_type === ItemEffectValueType::PERCENT,
                        source: $source,
                    );
                }
            }
        }

        // Weapon damage — flat modifiers that replace the zero base
        if ($equip->handLeft instanceof Item) {
            $source = 'equipment:' . $equip->handLeft->itemInfo->name;
            foreach ($equip->handLeft->itemInfo->effects as $effect) {
                match ($effect->effect_type) {
                    ItemEffectType::ATTACK_MIN => $modifiers[] = new StatModifier('left_min_dmg', $effect->value, false, $source),
                    ItemEffectType::ATTACK_MAX => $modifiers[] = new StatModifier('left_max_dmg', $effect->value, false, $source),
                    default => null,
                };
            }
        }

        if ($equip->handRight instanceof Item) {
            $source = 'equipment:' . $equip->handRight->itemInfo->name;
            foreach ($equip->handRight->itemInfo->effects as $effect) {
                match ($effect->effect_type) {
                    ItemEffectType::ATTACK_MIN => $modifiers[] = new StatModifier('right_min_dmg', $effect->value, false, $source),
                    ItemEffectType::ATTACK_MAX => $modifiers[] = new StatModifier('right_max_dmg', $effect->value, false, $source),
                    default => null,
                };
            }
        }

        return $modifiers;
    }

    /** @return StatModifier[] */
    private function fromPassiveSkills(Player $player): array
    {
        $modifiers = [];

        $passiveSkills = $player->magicSkills()
            ->where('is_passive', true)
            ->wherePivot('is_equipped', true)
            ->with('skillEffects')
            ->get();

        foreach ($passiveSkills as $skill) {
            $source = 'passive:' . $skill->name;

            // Source 1: magic_skills.effects (JSON array of stat modifier objects)
            foreach ($skill->effects ?? [] as $entry) {
                if (!is_array($entry)) continue;
                array_push($modifiers, ...$this->modifiersFromEntry($entry, $source));
            }

            // Source 2: related Effect records via magic_skill_effects
            foreach ($skill->skillEffects as $effect) {
                foreach ($effect->stat_modifiers ?? [] as $entry) {
                    if (!is_array($entry)) continue;
                    array_push($modifiers, ...$this->modifiersFromEntry($entry, $source));
                }
            }
        }

        return $modifiers;
    }

    /** @return StatModifier[] */
    private function fromBuffs(Player $player): array
    {
        // TODO: apply active PlayerEffect stat_modifiers when buff system is ready
        return [];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Parse one modifier entry: {"type": "attack", "value": 2, "is_percent": true}
     * "attack" expands to all 4 damage stats.
     *
     * @return StatModifier[]
     */
    private function modifiersFromEntry(array $entry, string $source): array
    {
        $type   = $entry['type']       ?? null;
        $value  = (float) ($entry['value']      ?? 0);
        $isPct  = (bool)  ($entry['is_percent'] ?? false);

        if (!$type) {
            return [];
        }

        if ($type === 'attack') {
            return [
                new StatModifier('left_min_dmg',  $value, $isPct, $source),
                new StatModifier('left_max_dmg',  $value, $isPct, $source),
                new StatModifier('right_min_dmg', $value, $isPct, $source),
                new StatModifier('right_max_dmg', $value, $isPct, $source),
            ];
        }

        return [new StatModifier($type, $value, $isPct, $source)];
    }
}